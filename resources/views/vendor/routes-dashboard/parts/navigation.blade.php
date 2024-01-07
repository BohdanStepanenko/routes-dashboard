<div data-collapse="medium" data-animation="default" data-duration="400" data-easing="ease" data-easing2="ease"
     role="banner" class="navigation w-nav">
    <div class="navigation-wrap"><a href="{{ request()->fullUrl() }}" class="logo-link w-nav-brand">
            <img src="{{ asset('vendor/routes-dashboard/images/logo.png') }}"
                 width="108" alt="" class="logo-image"/></a>
        <div class="menu">
            <a data-toggle="modal" data-target="#generateModal" class="button link w-inline-block">
                <div class="text-block">Generate &amp; Export</div>
            </a>
            <nav role="navigation" class="navigation-items w-nav-menu">
                <a data-toggle="modal" data-target="#healthModal" class="navigation-item w-nav-link w--current">
                    <span class="fa-icon-regular status-{{ $healthStatusInfo['status'] }}"></span>
                </a>
                <a href="https://github.com/BohdanStepanenko/routes-dashboard" target="_blank" aria-current="page" class="navigation-item w-nav-link w--current">
                    <span class="fa-brand"></span>
                </a>
            </nav>
        </div>
    </div>
</div>
