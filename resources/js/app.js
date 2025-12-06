import "./bootstrap";
import "../css/app.css";
import $ from "jquery";
import {
    createIcons,
    CircleAlert,
    SquarePen,
    Send,
    MoveLeft,
    Plus,
    MoveUp,
    MoveDown,
    Eye,
    Edit,
    ChevronLeft,
    ChevronRight,
    Search,
    RefreshCcw,
    FileCheck,
    LogOut,
    ArrowLeft,
    Menu,
    X,
    ClipboardClock,
    FileClock,
    Check,
    Bell,
    Home,
    Users,
    Building2,
    MapPin,
    ShieldCheck,
    FileText,
    Inbox,
    Clock,
    CircleCheckBig,
    XCircle,
    Ban,
    UsersRound,
    FolderOpen,
    UserRound,
    ImageOff,
    EllipsisVertical,
} from "lucide";

createIcons({
    icons: {
        CircleAlert,
        SquarePen,
        Send,
        MoveLeft,
        MoveUp,
        MoveDown,
        Plus,
        Eye,
        Edit,
        ChevronLeft,
        ChevronRight,
        Search,
        RefreshCcw,
        FileCheck,
        LogOut,
        ArrowLeft,
        Menu,
        X,
        FileClock,
        Check,
        Bell,
        Home,
        Users,
        Building2,
        MapPin,
        ShieldCheck,
        FileText,
        Inbox,
        Clock,
        CircleCheckBig,
        XCircle,
        Ban,
        UsersRound,
        FolderOpen,
        UserRound,
        ImageOff,
        EllipsisVertical
    },
});

window.$ = window.jQuery = $;

document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(() => {
        createIcons();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});