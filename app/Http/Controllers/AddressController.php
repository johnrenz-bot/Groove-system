<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AddressController extends Controller
{
    public function show(Request $request)
    {
        // prefer explicit ?return=..., else use session flag set by Client/Coach screen
        $returnTo = $request->query('return', $request->session()->get('address_return'));

        if (!in_array($returnTo, ['client','coach'], true)) {
            // no guessing; tell the user/dev what’s wrong
            return back()->withErrors(['return' => 'Missing or invalid return target (client/coach).']);
        }

        // keep it in session as guard
        $request->session()->put('address_return', $returnTo);

        return view('ConfirmAddress', ['returnTo' => $returnTo]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'region_code'   => 'required',
            'province_code' => 'required',
            'city_code'     => 'required',
            'barangay_code' => 'required',
            'region_name'   => 'required',
            'province_name' => 'required',
            'city_name'     => 'required',
            'barangay_name' => 'required',
            'street'        => 'nullable',
            'postal_code'   => 'nullable',
            'return'        => 'nullable|string|in:client,coach',
        ]);

        // normalize city_code
        $rawCity  = $data['city_code'];
        $cityCode = $rawCity;
        $cityKind = null;
        if (str_contains($rawCity, ':')) {
            [$cityKind, $cityCode] = explode(':', $rawCity, 2);
            $cityKind = strtolower($cityKind);
        }

        // store selected address (exclude 'return')
        $payload = Arr::except($data, ['return']);
        $payload['city_code'] = $cityCode;
        $payload['city_kind'] = in_array($cityKind, ['city','municipality'], true) ? $cityKind : null;
        $request->session()->put('selected_address', $payload);

        // resolve target strictly (no default):
        $return = $request->input('return', $request->session()->get('address_return'));
        if (!in_array($return, ['client','coach'], true)) {
            return back()->withErrors(['return' => 'Missing or invalid return target (client/coach).']);
        }

        // one-time flag cleanup
        $request->session()->forget('address_return');

        $routeMap = ['client' => 'Clientregister', 'coach' => 'CoachRegister'];
        return redirect()->route($routeMap[$return])->with('addressConfirmed', true);
    }
}
