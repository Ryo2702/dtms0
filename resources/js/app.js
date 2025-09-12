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
    },
});

window.$ = window.jQuery = $;
