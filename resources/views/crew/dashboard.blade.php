@extends('layouts.master')

@section('page_title', 'My Schedule')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">My Schedule</h3>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fs-6 text-muted">Next Flight</span>
                        <i class="bi bi-airplane-engines fs-2 text-primary"></i>
                    </div>
                    @if($nextFlight)
                        <div class="fs-5 fw-bold">{{ $nextFlight->flight_number }} — {{ $nextFlight->origin }} → {{ $nextFlight->destination }}</div>
                        <div class="text-gray-600 mt-2">Departs: {{ optional($nextFlight->departure_time)->format('d M Y H:i') }}</div>
                    @else
                        <div class="text-gray-600">Tidak ada jadwal mendatang.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fs-6 text-muted">Certifications</span>
                        <i class="bi bi-patch-check fs-2 text-success"></i>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <div>
                            <div class="fs-2 fw-bold text-success">{{ $certValid }}</div>
                            <div class="text-gray-600">Valid</div>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold text-danger">{{ $certExpired }}</div>
                            <div class="text-gray-600">Expired</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fs-6 text-muted">Health Status</span>
                        <i class="bi bi-heart-pulse fs-2 text-danger"></i>
                    </div>
                    @if($latestHealth)
                        <div class="fs-5 fw-bold text-{{ $latestHealth->status === 'fit' ? 'success' : 'warning' }} text-capitalize">{{ $latestHealth->status }}</div>
                        <div class="text-gray-600 mt-2">Last checkup: {{ optional($latestHealth->checkup_date)->format('d M Y') }}</div>
                    @else
                        <div class="text-gray-600">Belum ada data kesehatan.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a id="tab-list" class="nav-link active" data-bs-toggle="tab" href="#kt_tab_list" role="tab">List View</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a id="tab-calendar" class="nav-link" data-bs-toggle="tab" href="#kt_tab_calendar" role="tab">Calendar View</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="tab-content" id="kt_tab_content">
                <div class="tab-pane fade show active" id="kt_tab_list" role="tabpanel">
                    <div class="table-responsive">
                        <table id="crew_schedule_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <tr>
                                    <th>Flight</th>
                                    <th>Route</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @forelse($schedules as $flight)
                                    <tr class="js-flight-row" data-id="{{ $flight->id }}" style="cursor:pointer;">
                                        <td>{{ $flight->flight_number }}</td>
                                        <td>{{ $flight->origin }} → {{ $flight->destination }}</td>
                                        <td>{{ optional($flight->departure_time)->format('d M Y H:i') }}</td>
                                        <td>{{ optional($flight->arrival_time)->format('d M Y H:i') }}</td>
                                        <td class="text-capitalize">{{ $flight->status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada jadwal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="kt_tab_calendar" role="tabpanel">
                    <div id="kt_calendar_app" class="mt-5"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="flightDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flight Detail</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="flightDetailBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function(){
        // DataTables init
        if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
            jQuery('#crew_schedule_table').DataTable({
                pageLength: 10,
                ordering: true,
                language: { url: '' }
            });
        }

        // Row click to open detail modal
        document.querySelectorAll('#crew_schedule_table tbody tr.js-flight-row').forEach(function(tr){
            tr.addEventListener('click', function(){
                var id = this.getAttribute('data-id');
                if (id) renderFlightDetail(id);
            });
        });

        // FullCalendar init
        var calendar, calendarEl = document.getElementById('kt_calendar_app');
        var events = @json($calendarEvents ?? []);
        var scheduleDetails = @json($scheduleDetails ?? []);

        function renderFlightDetail(id){
            var d = scheduleDetails[id];
            if (!d) return;
            var crewsHtml = (d.crews || []).map(function(c){
                return '<tr>'+
                    '<td>'+ (c.name || '-') +'</td>'+
                    '<td>'+ (c.position || '-') +'</td>'+
                '</tr>';
            }).join('');
            if (!crewsHtml) crewsHtml = '<tr><td colspan="2" class="text-center text-muted">No crew listed</td></tr>';

            var html = ''+
                '<div class="row g-5">'+
                    '<div class="col-md-6">'+
                        '<div class="border rounded p-4 h-100">'+
                            '<div class="d-flex align-items-center justify-content-between mb-3">'+
                                '<span class="fs-7 text-muted">Flight</span>'+
                                '<i class="bi bi-airplane-engines fs-2 text-primary"></i>'+
                            '</div>'+
                            '<div class="fs-3 fw-bold mb-2">'+ d.flight_number +'</div>'+
                            '<div class="text-gray-700">'+ d.origin +' → '+ d.destination +'</div>'+
                            '<div class="text-gray-700 mt-2">Departure: <strong>'+ (d.departure_time || '-') +'</strong></div>'+
                            '<div class="text-gray-700">Arrival: <strong>'+ (d.arrival_time || '-') +'</strong></div>'+
                            '<div class="mt-3"><span class="badge badge-light-'+ (d.status === 'scheduled' ? 'primary' : (d.status === 'active' ? 'info' : (d.status === 'completed' ? 'success' : 'danger'))) +' text-capitalize">'+ d.status +'</span></div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="col-md-6">'+
                        '<div class="border rounded p-4 h-100">'+
                            '<div class="d-flex align-items-center justify-content-between mb-3">'+
                                '<span class="fs-7 text-muted">Assigned Crew</span>'+
                                '<i class="bi bi-people fs-2 text-dark"></i>'+
                            '</div>'+
                            '<div class="table-responsive">'+
                                '<table class="table align-middle table-row-dashed fs-6 gy-3">'+
                                    '<thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">'+
                                        '<tr><th>Name</th><th>Position</th></tr>'+
                                    '</thead>'+
                                    '<tbody>'+ crewsHtml +'</tbody>'+
                                '</table>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';

            var body = document.getElementById('flightDetailBody');
            if (body) body.innerHTML = html;
            var modalEl = document.getElementById('flightDetailModal');
            if (modalEl && window.bootstrap) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
        function initCalendar(){
            if (!window.FullCalendar || !calendarEl) return;
            if (calendar) return; // init once
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 650,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
                eventClick: function(info){
                    if (info && info.jsEvent && info.jsEvent.preventDefault) info.jsEvent.preventDefault();
                    var id = info && info.event ? info.event.id : null;
                    if (id) renderFlightDetail(id);
                }
            });
        }

        // Tabs behavior: render calendar when shown
        var tabCalendar = document.getElementById('tab-calendar');
        var tabList = document.getElementById('tab-list');
        if (tabCalendar) {
            tabCalendar.addEventListener('shown.bs.tab', function(){
                initCalendar();
                setTimeout(function(){ calendar && calendar.render(); }, 50);
            });
        }
        if (tabList) {
            tabList.addEventListener('shown.bs.tab', function(){
                // nothing for list
            });
        }
    })();
</script>
@endpush
