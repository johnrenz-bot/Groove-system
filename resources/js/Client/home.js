// resources/js/Client/home.js


document.addEventListener("DOMContentLoaded", () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = today.getMonth();

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const totalDays = lastDay.getDate();
  const startDay = firstDay.getDay(); 

  const calendarDays = document.getElementById("calendar-days");
  if (!calendarDays) return;

  calendarDays.innerHTML = "";

  for (let i = 0; i < startDay; i++) {
    calendarDays.innerHTML += `<span></span>`;
  }

  for (let i = 1; i <= totalDays; i++) {
    const isToday = i === today.getDate();
    calendarDays.innerHTML += `
      <span class="py-1 rounded-md transition duration-150 ${
        isToday
          ? "bg-zinc-700 text-white font-bold"
          : "hover:bg-zinc-700 text-zinc-300"
      }">${i}</span>`;
  }
});

    document.addEventListener('DOMContentLoaded', () => {
        const hour = new Date().getHours();
        let greeting = "Hello";

        if (hour >= 5 && hour < 12) {
            greeting = "Good Morning";
        } else if (hour >= 12 && hour < 17) {
            greeting = "Good Afternoon";
        } else if (hour >= 17 && hour < 21) {
            greeting = "Good Evening";
        } else {
            greeting = "Good Night";
        }

        document.getElementById('greeting').textContent = greeting;
    });



