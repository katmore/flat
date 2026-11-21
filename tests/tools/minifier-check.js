'use strict';
const fs = require('fs');
const vm = require('vm');
const cases = fs.readFileSync(0, 'utf8').trim().split('\n').map(JSON.parse);
for (const test of cases) {
    for (const key of ['source', 'minified']) {
        const context = {};
        try {
            vm.runInNewContext(test[key], context, {timeout: 1000});
            if (JSON.stringify(context.result) !== JSON.stringify(test.expected)) {
                throw new Error(`expected ${JSON.stringify(test.expected)}, got ${JSON.stringify(context.result)}`);
            }
        } catch (error) {
            throw new Error(`${key}: ${test[key]}\n${error.message}`);
        }
    }
}
console.log(`${cases.length} minifier programs preserve their JavaScript results.`);
