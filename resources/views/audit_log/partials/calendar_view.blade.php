<div class="calendar-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 id="calendar-month-year" class="mb-0 text-capitalize"></h4>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" id="prev-month">
                <i class="ti ti-chevron-left"></i>
            </button>
            <button class="btn btn-outline-secondary" id="next-month">
                <i class="ti ti-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="calendar-grid" id="calendar-grid">
        {{-- Days will be rendered here via JS --}}
    </div>
</div>

{{-- Modal for Day Details --}}
<div class="modal fade" id="day-details-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="day-details-title">{{ __('События за день') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="day-details-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }

    .calendar-day-header {
        text-align: center;
        font-weight: bold;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .calendar-day {
        min-height: 120px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.2s;
        background-color: #fff;
        position: relative;
    }

    .calendar-day:hover {
        border-color: #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .calendar-day.empty {
        background-color: transparent;
        border: none;
        cursor: default;
    }

    .calendar-day.today {
        border: 2px solid #0d6efd;
    }

    .day-number {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    .event-dots {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
    }

    .event-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .more-events {
        font-size: 10px;
        color: #6c757d;
        margin-top: 2px;
        display: block;
    }
</style>

<script src="{{ asset('js/audit-calendar.js') }}"></script>
