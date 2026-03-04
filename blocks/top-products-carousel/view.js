import { store, getContext, getElement } from '@wordpress/interactivity';

store('darlingteatime/top-products-carousel', {
    actions: {
        init: () => {
            const context = getContext();
            const { ref } = getElement();
            const track = ref; // Because data-wp-init is on the track div

            if (!track) return;

            // Set up autoscroll
            const scrollStep = 1; // Pixels per frame

            const autoScroll = () => {
                if (context.isAutoscrolling) {
                    track.scrollLeft += scrollStep;

                    // Reset scroll if we've reached the end
                    // Use Math.ceil to handle fractional subpixel calculations on high DPI screens
                    if (Math.ceil(track.scrollLeft + track.clientWidth) >= track.scrollWidth) {
                        track.scrollLeft = 0;
                    }
                }

                requestAnimationFrame(autoScroll);
            };

            // Start the loop
            requestAnimationFrame(autoScroll);
        },
        pauseAutoscroll: () => {
            const context = getContext();
            context.isAutoscrolling = false;
        },
        resumeAutoscroll: () => {
            const context = getContext();
            context.isAutoscrolling = true;
        }
    }
});
