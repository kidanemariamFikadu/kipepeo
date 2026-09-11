import confetti from 'canvas-confetti';

// Birthday check-in celebration: a few random bursts across the top of the
// screen, like fireworks, layered over everything (including the modal).
window.KipepeoFireworks = (function () {
    function burst() {
        const duration = 2500;
        const end = Date.now() + duration;
        const colors = ['#2a78d6', '#1baf7a', '#eda100', '#e34948', '#e87ba4'];

        (function frame() {
            confetti({
                particleCount: 3,
                angle: 60,
                spread: 65,
                origin: { x: 0, y: 0.6 },
                colors,
                zIndex: 9999,
            });
            confetti({
                particleCount: 3,
                angle: 120,
                spread: 65,
                origin: { x: 1, y: 0.6 },
                colors,
                zIndex: 9999,
            });

            if (Date.now() < end) {
                requestAnimationFrame(frame);
            }
        })();

        confetti({
            particleCount: 120,
            spread: 100,
            startVelocity: 45,
            origin: { x: 0.5, y: 0.3 },
            colors,
            zIndex: 9999,
        });
    }

    function play(name) {
        burst();

        return name;
    }

    return { play, burst };
})();
