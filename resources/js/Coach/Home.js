  resrouces/js/Coach/Home.blade.php

  const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const totalDays = lastDay.getDate();
    const startDay = firstDay.getDay();
    const calendarDays = document.getElementById("calendar-days");
    calendarDays.innerHTML = "";

    for (let i = 0; i < startDay; i++) calendarDays.innerHTML += `<span></span>`;
    for (let i = 1; i <= totalDays; i++) {
      const isToday = i === today.getDate();
      calendarDays.innerHTML += `<span class="py-1 rounded-md ${isToday ? 'bg-zinc-700 text-white font-bold' : 'hover:bg-zinc-700'}">${i}</span>`;
    }
