<?php

namespace App\Enums;

use Filament\Support\Icons\Heroicon;

/**
 * Satu set ikon untuk seluruh aplikasi: Heroicons (set bawaan Filament), sehingga panel Admin
 * dan portal Blade memakai ikon yang sama. Nama case bersifat semantik; komponen Blade
 * <x-atoms.icon> dan Resource/Page Filament sama-sama membaca ikon dari enum ini.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → ICON.
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
    case Info = 'info';
    case RotateCw = 'rotate-cw';
    case Eye = 'eye';
    case EyeOff = 'eye-off';
    case CircleCheck = 'circle-check';
    case LoaderCircle = 'loader-circle';

    public function heroicon(): Heroicon
    {
        return match ($this) {
            self::LayoutDashboard => Heroicon::OutlinedHome,
            self::Users => Heroicon::OutlinedUsers,
            self::Building => Heroicon::OutlinedBuildingOffice2,
            self::Presentation => Heroicon::OutlinedAcademicCap,
            self::ClipboardList => Heroicon::OutlinedUserGroup,
            self::SquareUser => Heroicon::OutlinedBriefcase,
            self::Table => Heroicon::OutlinedTableCells,
            self::PanelLeft => Heroicon::OutlinedBars3BottomLeft,
            self::Menu => Heroicon::OutlinedBars3,
            self::X => Heroicon::OutlinedXMark,
            self::LogOut => Heroicon::OutlinedArrowLeftEndOnRectangle,
            self::ChevronDown => Heroicon::OutlinedChevronDown,
            self::ChevronLeft => Heroicon::OutlinedChevronLeft,
            self::ChevronRight => Heroicon::OutlinedChevronRight,
            self::TriangleAlert => Heroicon::OutlinedExclamationTriangle,
            self::Info => Heroicon::OutlinedInformationCircle,
            self::RotateCw => Heroicon::OutlinedArrowPath,
            self::Eye => Heroicon::OutlinedEye,
            self::EyeOff => Heroicon::OutlinedEyeSlash,
            self::CircleCheck => Heroicon::OutlinedCheckCircle,
            self::LoaderCircle => Heroicon::OutlinedArrowPath,
        };
    }

    /**
     * Nama ikon untuk helper svg() blade-icons, mis. "heroicon-o-users".
     */
    public function svgName(): string
    {
        return 'heroicon-'.$this->heroicon()->value;
    }
}
