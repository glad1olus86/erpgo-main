document.addEventListener('DOMContentLoaded', function () {
    const calendarGrid = document.getElementById('calendar-grid');
    const monthYearTitle = document.getElementById('calendar-month-year');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const dayDetailsModal = new bootstrap.Modal(document.getElementById('day-details-modal'));
    const dayDetailsBody = document.getElementById('day-details-body');
    const dayDetailsTitle = document.getElementById('day-details-title');

    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth() + 1; // 1-12

    // Event colors mapping
    const eventColors = {
        'worker.created': '#28a745',
        'worker.updated': '#17a2b8',
        'worker.deleted': '#6c757d',
        'worker.checked_in': '#007bff',
        'worker.checked_out': '#fd7e14',
        'worker.hired': '#6f42c1',
        'worker.dismissed': '#dc3545',
        'room.created': '#20c997',
        'room.updated': '#17a2b8',
        'room.deleted': '#6c757d',
        'work_place.created': '#20c997',
        'work_place.updated': '#17a2b8',
        'work_place.deleted': '#6c757d',
        'hotel.created': '#28a745',
        'hotel.updated': '#17a2b8',
        'hotel.deleted': '#6c757d',
    };

    function fetchCalendarData(year, month) {
        // Show loading state
        calendarGrid.style.opacity = '0.5';

        fetch(`/audit/calendar/${year}/${month}`)
            .then(response => response.json())
            .then(data => {
                renderCalendar(data);
                calendarGrid.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching calendar data:', error);
                calendarGrid.style.opacity = '1';
            });
    }

    function renderCalendar(data) {
        calendarGrid.innerHTML = '';

        const date = new Date(data.year, data.month - 1, 1);
        const monthName = date.toLocaleString('ru-RU', { month: 'long', year: 'numeric' });
        monthYearTitle.textContent = monthName;

        // Day headers
        const daysOfWeek = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        daysOfWeek.forEach(day => {
            const header = document.createElement('div');
            header.className = 'calendar-day-header';
            header.textContent = day;
            calendarGrid.appendChild(header);
        });

        // Empty cells before first day
        let firstDay = date.getDay(); // 0 (Sun) - 6 (Sat)
        firstDay = firstDay === 0 ? 6 : firstDay - 1; // Convert to Mon (0) - Sun (6)

        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyCell);
        }

        // Days
        const daysInMonth = new Date(data.year, data.month, 0).getDate();

        for (let i = 1; i <= daysInMonth; i++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'calendar-day';

            // Check if today
            const today = new Date();
            if (today.getDate() === i && today.getMonth() + 1 === data.month && today.getFullYear() === data.year) {
                dayCell.classList.add('today');
            }

            const dayNumber = document.createElement('span');
            dayNumber.className = 'day-number';
            dayNumber.textContent = i;
            dayCell.appendChild(dayNumber);

            // Events dots
            if (data.days[i]) {
                const eventsContainer = document.createElement('div');
                eventsContainer.className = 'event-dots';

                let dotCount = 0;
                const maxDots = 10;

                for (const [eventType, count] of Object.entries(data.days[i].events)) {
                    for (let j = 0; j < count; j++) {
                        if (dotCount < maxDots) {
                            const dot = document.createElement('span');
                            dot.className = 'event-dot';
                            dot.style.backgroundColor = eventColors[eventType] || '#6c757d';
                            dot.title = eventType;
                            eventsContainer.appendChild(dot);
                            dotCount++;
                        }
                    }
                }

                if (data.days[i].total > maxDots) {
                    const more = document.createElement('span');
                    more.className = 'more-events';
                    more.textContent = `+${data.days[i].total - maxDots}`;
                    eventsContainer.appendChild(more);
                }

                dayCell.appendChild(eventsContainer);
            }

            // Click handler
            dayCell.addEventListener('click', () => {
                openDayDetails(data.year, data.month, i);
            });

            calendarGrid.appendChild(dayCell);
        }
    }

    function openDayDetails(year, month, day) {
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const formattedDate = new Date(year, month - 1, day).toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        dayDetailsTitle.textContent = `События за ${formattedDate}`;
        dayDetailsBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        dayDetailsModal.show();

        fetch(`/audit/day/${dateStr}`)
            .then(response => response.text())
            .then(html => {
                dayDetailsBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching day details:', error);
                dayDetailsBody.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных</div>';
            });
    }

    // Navigation handlers
    prevMonthBtn.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        fetchCalendarData(currentYear, currentMonth);
    });

    nextMonthBtn.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        fetchCalendarData(currentYear, currentMonth);
    });

    // Initial load
    // Only load if the calendar tab is active or when it becomes active
    const calendarTab = document.getElementById('pills-calendar-tab');
    if (calendarTab) {
        calendarTab.addEventListener('shown.bs.tab', function (e) {
            fetchCalendarData(currentYear, currentMonth);
        });
    }
});
