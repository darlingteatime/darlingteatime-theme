import { store, getContext, getElement } from '@wordpress/interactivity';

store('darlingteatime/custom-project-carousel', {
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

                    // Measure the exact offset where the cloned items start.
                    // The first cloned item is items.length index in the total children.
                    const firstClone = track.children[items.length];

                    if (firstClone && track.scrollLeft >= firstClone.offsetLeft) {
                        // Snap back to exactly where we started relative to the clone
                        track.scrollLeft -= firstClone.offsetLeft;
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
