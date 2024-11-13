function initializeCanvas(canvasId) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    canvas.addEventListener('mousedown', (e) => {
        isDrawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });

    canvas.addEventListener('mousemove', (e) => {
        if (isDrawing) {
            const x = e.offsetX;
            const y = e.offsetY;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            [lastX, lastY] = [x, y];
        }
    });

    canvas.addEventListener('mouseup', () => {
        isDrawing = false;
    });

    canvas.addEventListener('mouseout', () => {
        isDrawing = false;
    });

    return { canvas, ctx };
}

// Initialize canvases
const lead = initializeCanvas('leadSignatureCanvas');
const adviser = initializeCanvas('adviserSignatureCanvas');
const chairperson = initializeCanvas('chairpersonSignatureCanvas');
const dean = initializeCanvas('deanSignatureCanvas');
const ices = initializeCanvas('icesSignatureCanvas');
const ministry = initializeCanvas('ministrySignatureCanvas');
const sds = initializeCanvas('sdsSignatureCanvas');
const osa = initializeCanvas('osaSignatureCanvas');
const vpsa = initializeCanvas('vpsaSignatureCanvas');
const vpfa = initializeCanvas('vpfaSignatureCanvas');