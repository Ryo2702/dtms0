/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                // Custom DTMS colors
                "dtms-bg": "#F5EEDC",
                "dtms-primary": "#27548A",
                "dtms-secondary": "#183B4E",
                "dtms-accent": "#DDA853",
            },
        },
    },
    plugins: [require("daisyui")],
    daisyui: {
        themes: [
            {
                dtms: {
                    primary: "#27548A",
                    "primary-content": "#ffffff",
                    secondary: "#183B4E",
                    "secondary-content": "#ffffff",
                    accent: "#DDA853",
                    "accent-content": "#ffffff",
                    neutral: "#3D4451",
                    "neutral-content": "#ffffff",
                    "base-100": "#F5EEDC",
                    "base-200": "#E5D5C2",
                    "base-300": "#D4C4A8",
                    "base-content": "#1f2937",
                    info: "#3ABFF8",
                    success: "#36D399",
                    warning: "#FBBD23",
                    error: "#F87272",
                },
            },
        ],
        base: true,
        styled: true,
        utils: true,
    },
};
