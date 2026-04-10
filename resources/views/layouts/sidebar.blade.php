@php
    // Permission-based rendering (RBAC Hybrid)
@endphp

<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <!--begin::Menu wrapper-->
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <!--begin::Scroll wrapper-->
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true"
            data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-save-state="true">

            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">

                @if ((auth()->user()?->role ?? null) === 'admin')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <span class="menu-icon">
                                <i class="bi bi-grid fs-2"></i>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.crew.*') ? 'active' : '' }}"
                            href="{{ route('admin.crew.index') }}">
                            <span class="menu-icon">
                                <i class="bi bi-people fs-2"></i>
                            </span>
                            <span class="menu-title">Manage Crew</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.flight-schedules.*') ? 'active' : '' }}" href="{{ route('admin.flight-schedules.index') }}">
                            <span class="menu-icon">
                                <i class="bi bi-airplane-engines fs-2"></i>
                            </span>
                            <span class="menu-title">Flight Schedules</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                            <span class="menu-icon">
                                <i class="bi bi-bar-chart fs-2"></i>
                            </span>
                            <span class="menu-title">Reports</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('crew.dashboard') ? 'active' : '' }}"
                            href="{{ route('crew.dashboard') }}">
                            <span class="menu-icon">
                                <i class="bi bi-grid fs-2"></i>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>
                @endif

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Scroll wrapper-->
    </div>
    <!--end::Menu wrapper-->
</div>
