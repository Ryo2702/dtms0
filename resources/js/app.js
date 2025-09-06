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
    },
});

window.$ = window.jQuery = $;
