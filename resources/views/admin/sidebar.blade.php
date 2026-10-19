@php

use \Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
$rolesSubmenu = [];
$user = Auth::user();
foreach (Role::all() as $role) {
    if ($role->name == 'Employers' || $role->name == 'Candidates') {
        continue;
    }
    $rolesSubmenu[] = [
        'title' => ucwords($role->name),
        'link' =>route('admin.user_list',Str::slug($role->name)),
        'is_active' => Request::is('admin/user/'.$role->name),
        'permission' => 'user_list',
    ];
}


    $sidebar_menu_links = [
        [
            'title' => 'Dashboard',
            'icon' => <<<HTML
            <i class="ri-dashboard-line"></i>
            HTML,
            'link' => route('admin.dashboard.index'),
            'is_active' => Request::is('admin/dashboard'),
            'permission' => 'dashboard',
        ],
        [
            'title' => 'Manage Users',
            'icon' => <<<HTML
            <i class="ri-group-line"></i>
            HTML,
            'link' => "#",
            'is_active' => Request::is('admin/user/*'),
            'submenu' => $rolesSubmenu,
            'permission' => 'users',
        ],
        [
           'title' => 'Employers',
            'icon' => <<<HTML
            <i class="ri-group-line"></i>
            HTML,
            'link' => route('admin.employers.index'),
            'is_active' => Request::is('admin/employers/*') || Request::is('admin/employers'),
            'permission' => 'employers.index',
        ],
        [
           'title' => 'Candidates',
            'icon' => <<<HTML
            <i class="ri-creative-commons-by-line"></i>
            HTML,
            'link' => route('admin.candidates.index'),
            'is_active' => Request::is('admin/candidates/*') || Request::is('admin/candidates'),
            'permission' => 'candidates.index',
        ],
        [
           'title' => 'Resume Inbox',
            'icon' => <<<HTML
            <i class="ri-mail-open-line"></i>
            HTML,
            'link' => route('admin.resume-inbox.index'),
            'is_active' => Request::is('admin/resume-inbox*'),
            'permission' => 'resume-inbox.index',
        ],
        [
           'title' => 'Languages',
            'icon' => <<<HTML
            <i class="ri-translate-2"></i>
            HTML,
            'link' => route('admin.languages.index'),
            'is_active' => Request::is('admin/languages/*') || Request::is('admin/languages'),
            'permission' => 'languages.index',
        ],
        [
           'title' => 'Skills',
            'icon' => <<<HTML
            <i class="ri-code-line"></i>
            HTML,
            'link' => route('admin.skills.index'),
            'is_active' => Request::is('admin/skills/*') || Request::is('admin/skills'),
            'permission' => 'skills.index',
        ],
        [
           'title' => 'Jobs',
            'icon' => <<<HTML
            <i class="ri-projector-line"></i>
            HTML,
            'link' => route('admin.jobs.index'),
            'is_active' => Request::is('admin/jobs/*') || Request::is('admin/jobs'),
            'permission' => 'jobs.index',
        ],
        [
           'title' => 'Email Template',
            'icon' => <<<HTML
            <i class="ri-mail-settings-line"></i>
            HTML,
            'link' => route('admin.email-template.index'),
            'is_active' => Request::is('admin/email-template/*') || Request::is('admin/email-template'),
            'permission' => 'email-template.index',
        ],
        [
           'title' => 'Manage Industries',
            'icon' => <<<HTML
            <i class="ri-building-4-line"></i>
            HTML,
            'link' => '#',
            'is_active' => Request::is('admin/industries/*') || Request::is('admin/industries'),
            'permission' => 'industries.index',
            'submenu' =>
            [
               [
                    'title' => 'Industry Type',
                    'icon' => <<<HTML
                    <i class="ri-layout-grid-line"></i>
                    HTML,
                    'link' => route('admin.industry-type.index'),
                    'is_active' => Request::is('admin/industry-type/*') || Request::is('admin/industry-type'),
                    'permission' => 'industry-type.index',
                ],
                [
                   'title' => 'Industries',
                    'icon' => <<<HTML
                    <i class="ri-building-4-line"></i>
                    HTML,
                    'link' => route('admin.industries.index'),
                    'is_active' => Request::is('admin/industries/*') || Request::is('admin/industries'),
                    'permission' => 'industries.index',
               ],
               [
                    'title' => 'Functional Areas',
                    'icon' => <<<HTML
                    <i class="ri-layout-grid-line"></i>
                    HTML,
                    'link' => route('admin.functional-areas.index'),
                    'is_active' => Request::is('admin/functional-areas/*') || Request::is('admin/functional-areas'),
                    'permission' => 'functional-areas.index',
                ],
            ]
        ],

        [
           'title' => 'Settings',
           'icon' => <<<HTML
           <i class="ri-settings-line"></i>
           HTML,
           'link' => route('admin.roles.index'),
           'is_active' => Request::is('admin/roles') || Request::is('admin/role/*') ||    Request::is('admin/permissions'),
           'permission' => 'roles.index',
           'submenu' => [
                [
                    'title' => 'Create Roles',
                    'link' => route('admin.roles.index'),
                    'is_active' => Request::is('admin/roles'),
                    'permission' => 'roles.index',
                ],
                [
                    'title' => 'Create Permission',
                    'link' => route('admin.permissions.index'),
                    'is_active' => Request::is('admin/permissions'),
                    'permission' => 'permissions.index',
                ],
                [
                    'title' => 'Roles & Permission',
                    'link' => route('admin.roles.assing_permission'),
                    'is_active' => Request::is('admin/role/assing-permission'),
                    'permission' => 'roles.assing_permission',
                ],
            ]
        ],
    ];
@endphp



@include("admin.layouts.sidebar")
