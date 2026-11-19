<?php

namespace App\Enums;

/**
 * Set ikon SVG inline (bentuk dari Lucide, lisensi ISC). Memakai enum agar nama ikon
 * tervalidasi saat kompilasi, bukan string bebas yang baru gagal saat runtime.
 */
enum Icon: string
{
    case LayoutDashboard = 'layout-dashboard';
    case Users = 'users';
    case Building = 'building';
    case Presentation = 'presentation';
    case ClipboardList = 'clipboard-list';
    case SquareUser = 'square-user';
    case Table = 'table';
    case PanelLeft = 'panel-left';
    case Menu = 'menu';
    case X = 'x';
    case LogOut = 'log-out';
    case ChevronDown = 'chevron-down';
    case ChevronLeft = 'chevron-left';
    case ChevronRight = 'chevron-right';
    case TriangleAlert = 'triangle-alert';
    case RotateCw = 'rotate-cw';
    case Eye = 'eye';
    case EyeOff = 'eye-off';
    case CircleCheck = 'circle-check';
    case LoaderCircle = 'loader-circle';

    public function paths(): string
    {
        return match ($this) {
            self::LayoutDashboard => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
            self::Users => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            self::Building => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/>',
            self::Presentation => '<path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/>',
            self::ClipboardList => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
            self::SquareUser => '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 21v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>',
            self::Table => '<path d="M12 3v18"/><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/>',
            self::PanelLeft => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/>',
            self::Menu => '<line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>',
            self::X => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            self::LogOut => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
            self::ChevronDown => '<path d="m6 9 6 6 6-6"/>',
            self::ChevronLeft => '<path d="m15 18-6-6 6-6"/>',
            self::ChevronRight => '<path d="m9 18 6-6-6-6"/>',
            self::TriangleAlert => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            self::RotateCw => '<path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>',
            self::Eye => '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>',
            self::EyeOff => '<path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>',
            self::CircleCheck => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            self::LoaderCircle => '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>',
        };
    }
}
