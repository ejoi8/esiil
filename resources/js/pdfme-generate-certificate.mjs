import { generate } from '@pdfme/generator';
import { image, text } from '@pdfme/schemas';

const readInput = async () =>
    new Promise((resolve, reject) => {
        let buffer = '';

        process.stdin.setEncoding('utf8');
        process.stdin.on('data', (chunk) => {
            buffer += chunk;
        });
        process.stdin.on('end', () => {
            resolve(buffer);
        });
        process.stdin.on('error', reject);
    });

const main = async () => {
    const rawPayload = await readInput();
    const { inputs, template, fonts = {} } = JSON.parse(rawPayload);
    const normalizedFonts = Object.fromEntries(
        Object.entries(fonts).map(([name, definition]) => [
            name,
            {
                ...definition,
                data: Buffer.from(definition.data, 'base64'),
            },
        ]),
    );

    const pdf = await generate({
        inputs: [inputs],
        template,
        options: {
            font: normalizedFonts,
        },
        plugins: {
            image,
            text,
        },
    });

    process.stdout.write(JSON.stringify({
        pdf: Buffer.from(pdf).toString('base64'),
    }));
};

main().catch((error) => {
    process.stderr.write((error?.stack || error?.message || String(error)));
    process.exit(1);
});
