import { store, getContext, getElement } from '@wordpress/interactivity';

store('darlingteatime/top-products-carousel', {
    actions: {
        init: () => {
            const context = getContext();
            const { ref } = getElement();
            const track = ref; // Because data-wp-init is on the track div

            if (!track) return;

            // Clone the initial items to create an infinite loop effect
            const items = Array.from(track.children);
            items.forEach((item) => {
                const clone = item.cloneNode(true);
                // Remove interactive attributes on clones if necessary, though
                // for simple links it's usually fine.
                track.appendChild(clone);
            });

            // Set up autoscroll
            const scrollStep = 1; // Pixels per frame

            const autoScroll = () => {
                if (context.isAutoscrolling) {
                    track.scrollLeft += scrollStep;

                    // The track's scrollWidth is now twice the size of the original content
                    // If we have scrolled exactly the width of the original content,
                    // we snap back to the beginning to loop seamlessly.
                    // Because we doubled the content, the middle is exactly scrollWidth / 2.
                    // If gap is used, it might be slightly different, so it's safer to measure
                    // the original items' total width.
                    // A simple approximation for a flex container with duplicated content:
                    if (track.scrollLeft >= track.scrollWidth / 2) {
                        // Snap back by exactly half the scrollWidth
                        track.scrollLeft -= track.scrollWidth / 2;
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
