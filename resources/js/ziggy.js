// Export Ziggy properly
const Ziggy = {"url":"http://localhost:8000","port":8000,"defaults":{},"routes":{ /* your full routes here */ }};

// Merge with any window.Ziggy
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}

export { Ziggy };
