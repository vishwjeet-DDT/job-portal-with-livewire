
<aside class="left-sidebar">
    <div class="scroll-sidebar">
        <nav class="sidebar-nav">
            @if (!empty($sidebar_menu_links))
                <ul id="sidebarnav">
                    @foreach ($sidebar_menu_links as $menu)
                        @can($menu['permission'])
                            <li class="sidebar-item {{ $menu['is_active'] ? 'active selected' : '' }}">
                                @if (!empty($menu['submenu']))
                                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="{{ $menu['is_active'] ? 'true' : 'false' }}">
                                        {!! $menu['icon'] !!}
                                        <span class="hide-menu">{{ $menu['title'] }}</span>
                                    </a>

                                    <ul aria-expanded="{{ $menu['is_active'] ? 'true' : 'false' }}" class="collapse first-level submenus {{ $menu['is_active'] ? 'show' : '' }}">
                                        @foreach ($menu['submenu'] as $submenu)
                                            @can($submenu['permission'])
                                                <li class="sidebar-item">
                                                    <a href="{{ $submenu['link'] }}" class="sidebar-link {{ $submenu['is_active'] ? 'active' : '' }}">
                                                        <i class="ri-arrow-right-line"></i>
                                                        <span class="hide-menu">{{ $submenu['title'] }}</span>
                                                    </a>
                                                </li>
                                            @endcan
                                        @endforeach
                                    </ul>
                                @else
                                    <a class="sidebar-link waves-effect waves-dark" href="{{ $menu['link'] }}">
                                        {!! $menu['icon'] !!}
                                        <span class="hide-menu">{{ $menu['title'] }}</span>
                                    </a>
                                @endif
                            </li>
                        @endcan
                    @endforeach
                </ul>
            @endif
        </nav>
    </div>
</aside>

