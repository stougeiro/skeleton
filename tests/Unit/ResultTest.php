<?php declare(strict_types=1);

use Sourcerer\Service\Result;

it('builds a successful result carrying its payload', function () {
    $result = Result::success(['a', 'b']);

    expect($result->isSuccess())->toBeTrue()
        ->and($result->data())->toBe(['a', 'b'])
        ->and($result->messages())->toBe([]);
});

it('builds a failed result carrying its messages', function () {
    $result = Result::failure(['name is required']);

    expect($result->isSuccess())->toBeFalse()
        ->and($result->data())->toBeNull()
        ->and($result->messages())->toBe(['name is required']);
});

it('keeps the payload of a failed result', function () {
    $result = Result::failure(['invalid'], ['field' => 'name']);

    expect($result->isSuccess())->toBeFalse()
        ->and($result->data())->toBe(['field' => 'name'])
        ->and($result->messages())->toBe(['invalid']);
});
