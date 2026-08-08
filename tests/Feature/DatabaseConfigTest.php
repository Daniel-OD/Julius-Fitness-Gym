<?php

test('sqlite connection keeps :memory: instead of resolving it as a file path', function (): void {
    expect(config('database.connections.sqlite.database'))->toBe(':memory:');
});
