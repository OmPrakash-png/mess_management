/**
 * Mess Management Modern UI & Theme Controller
 */

// Initialize theme on earliest load
(function() {
  const savedTheme = localStorage.getItem('mess_theme') || 'sunset';
  document.documentElement.setAttribute('data-theme', savedTheme);
})();

document.addEventListener('DOMContentLoaded', () => {
  initThemeSwitcher();
  initLiveClockAndMeal();
  initStarRatings();
  initMenuFilters();
  initToast();
});

/* ===================================================
   1. THEME SWITCHER
   =================================================== */
function initThemeSwitcher() {
  const currentTheme = localStorage.getItem('mess_theme') || 'sunset';
  applyTheme(currentTheme);

  const themeBtn = document.getElementById('themeDropdownBtn');
  const themeMenu = document.getElementById('themeDropdownMenu');

  if (themeBtn && themeMenu) {
    themeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      themeMenu.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!themeMenu.contains(e.target) && !themeBtn.contains(e.target)) {
        themeMenu.classList.remove('show');
      }
    });

    document.querySelectorAll('.theme-opt-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const theme = btn.getAttribute('data-set-theme');
        applyTheme(theme);
        themeMenu.classList.remove('show');
      });
    });
  }
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('mess_theme', theme);

  document.querySelectorAll('.theme-opt-btn').forEach(btn => {
    if (btn.getAttribute('data-set-theme') === theme) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });

  const labelElem = document.getElementById('activeThemeLabel');
  if (labelElem) {
    const names = {
      sunset: 'Sunset Ember',
      midnight: 'Midnight Neon',
      emerald: 'Fresh Emerald',
      ocean: 'Ocean Azure',
      light: 'Light Luxe'
    };
    labelElem.textContent = names[theme] || 'Theme';
  }
}

/* ===================================================
   2. LIVE CLOCK & ACTIVE MEAL TRACKER
   =================================================== */
function initLiveClockAndMeal() {
  const clockElem = document.getElementById('liveClock');

  function updateTimeAndMeal() {
    const now = new Date();
    
    // Update live clock
    if (clockElem) {
      const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
      clockElem.textContent = now.toLocaleTimeString([], options);
    }

    // Determine current or next meal
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const currentTimeVal = hours + minutes / 60;

    let activeMeal = null;
    let nextMeal = null;

    if (currentTimeVal >= 7.0 && currentTimeVal < 10.5) {
      activeMeal = 'Breakfast';
      nextMeal = 'Lunch (12:30 PM)';
    } else if (currentTimeVal >= 10.5 && currentTimeVal < 12.0) {
      nextMeal = 'Lunch (12:30 PM)';
    } else if (currentTimeVal >= 12.0 && currentTimeVal < 15.5) {
      activeMeal = 'Lunch';
      nextMeal = 'Evening Snacks (4:30 PM)';
    } else if (currentTimeVal >= 15.5 && currentTimeVal < 16.5) {
      nextMeal = 'Evening Snacks (4:30 PM)';
    } else if (currentTimeVal >= 16.5 && currentTimeVal < 18.5) {
      activeMeal = 'Snacks';
      nextMeal = 'Dinner (8:00 PM)';
    } else if (currentTimeVal >= 18.5 && currentTimeVal < 19.5) {
      nextMeal = 'Dinner (8:00 PM)';
    } else if (currentTimeVal >= 19.5 && currentTimeVal < 22.5) {
      activeMeal = 'Dinner';
      nextMeal = 'Tomorrow Breakfast (7:30 AM)';
    } else {
      nextMeal = 'Breakfast (7:30 AM)';
    }

    // Highlight active card
    document.querySelectorAll('[data-meal-card]').forEach(card => {
      const mealName = card.getAttribute('data-meal-card');
      if (mealName === activeMeal) {
        card.classList.add('meal-active-card');
      } else {
        card.classList.remove('meal-active-card');
      }
    });

    const statusBadge = document.getElementById('liveMealStatusText');
    if (statusBadge) {
      if (activeMeal) {
        statusBadge.innerHTML = `<span class="pulse-badge"><span class="pulse-dot"></span> Serving Now: ${activeMeal}</span>`;
      } else {
        statusBadge.innerHTML = `<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-gray-800 text-gray-300 border border-gray-700">⏳ Next Up: ${nextMeal}</span>`;
      }
    }
  }

  updateTimeAndMeal();
  setInterval(updateTimeAndMeal, 1000);
}

/* ===================================================
   3. STAR RATING INTERACTIVITY
   =================================================== */
function initStarRatings() {
  const sentimentElem = document.getElementById('ratingSentimentText');
  const ratingInputs = document.querySelectorAll('input[name="rating"]');

  const sentiments = {
    '1': '😡 1/5 - Very Bad (Needs urgent improvement)',
    '2': '🙁 2/5 - Below Average',
    '3': '😐 3/5 - Average / Acceptable',
    '4': '😊 4/5 - Good & Tasty',
    '5': '🤩 5/5 - Outstanding & Delicious!'
  };

  ratingInputs.forEach(input => {
    input.addEventListener('change', () => {
      if (sentimentElem) {
        sentimentElem.textContent = sentiments[input.value] || '';
        sentimentElem.style.transform = 'scale(1.05)';
        setTimeout(() => sentimentElem.style.transform = 'scale(1)', 150);
      }
    });
  });
}

/* ===================================================
   4. MENU SEARCH & DAY FILTER TABS
   =================================================== */
function initMenuFilters() {
  // Day filter tabs for timetable
  const dayTabs = document.querySelectorAll('.day-tab-btn');
  const tableRows = document.querySelectorAll('#weeklyTableBody tr');

  dayTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      dayTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const selectedDay = tab.getAttribute('data-day');
      tableRows.forEach(row => {
        const rowDay = row.getAttribute('data-day');
        if (selectedDay === 'all' || rowDay === selectedDay) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  });

  // Search filter
  const searchInput = document.getElementById('menuSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      const q = e.target.value.toLowerCase().trim();
      tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (q === '' || text.includes(q)) {
          row.style.display = '';
          if (q !== '') {
            row.style.background = 'rgba(255, 94, 58, 0.12)';
          } else {
            row.style.background = '';
          }
        } else {
          row.style.display = 'none';
        }
      });
    });
  }
}

/* ===================================================
   5. AUTO-DISMISS TOAST
   =================================================== */
function initToast() {
  const toast = document.getElementById('toastNotification');
  if (toast) {
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(30px)';
      toast.style.transition = 'all 0.5s ease';
      setTimeout(() => toast.remove(), 500);
    }, 4500);
  }
}
