import { readFile } from 'node:fs/promises';
import { stdin, stdout } from 'node:process';
import { generate } from '@pdfme/generator';
import { image, text } from '@pdfme/schemas';

const chunks = [];

for await (const chunk of stdin) {
    chunks.push(chunk);
}

const payload = JSON.parse(Buffer.concat(chunks).toString('utf8'));
const fonts = Object.fromEntries(
    await Promise.all(
        Object.entries(payload.fonts ?? {}).map(async ([name, definition]) => [
            name,
            {
                data: new Uint8Array(await readFile(definition.path)),
                fallback: Boolean(definition.fallback),
                subset: definition.subset !== false,
            },
        ]),
    ),
);

const pdf = await generate({
    template: payload.template,
    inputs: [payload.inputs ?? {}],
    plugins: {
        image,
        text,
    },
    options: {
        font: fonts,
    },
});

stdout.write(Buffer.from(pdf).toString('base64'));
