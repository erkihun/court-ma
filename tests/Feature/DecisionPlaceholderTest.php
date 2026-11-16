<?php

declare(strict_types=1);

use App\Models\Decision;
use App\Support\DecisionPdf;

function placeholderDecision(array $attributes): Decision
{
    return (new Decision)->forceFill(array_merge([
        'case_number' => 'TC/00001/26',
        'panel_judges' => [],
    ], $attributes));
}

test('party names are html-escaped when filled into a decision template', function (): void {
    $decision = placeholderDecision([
        'respondent_full_name' => '<img src="http://169.254.169.254/latest">Acme',
        'applicant_full_name' => 'Abebe & Sons',
    ]);

    $body = DecisionPdf::fillPlaceholders('<p>{{ respondent_name }} v {{applicant_name}}</p>', $decision);

    expect($body)->toBe('<p>&lt;img src=&quot;http://169.254.169.254/latest&quot;&gt;Acme v Abebe &amp; Sons</p>');
});

test('decision content keeps its purified html', function (): void {
    $decision = placeholderDecision(['decision_content' => '<p><strong>Granted</strong></p>']);

    expect(DecisionPdf::fillPlaceholders('{{decision_content}}', $decision))
        ->toBe('<p><strong>Granted</strong></p>');
});

test('dollar signs and backslashes in values are inserted literally', function (): void {
    $decision = placeholderDecision([
        'decision_content' => '<p>Pay $100 and $1 fee per C:\\1 file</p>',
        'respondent_full_name' => 'Party $2',
    ]);

    $body = DecisionPdf::fillPlaceholders('{{decision_content}}|{{respondent_name}}', $decision);

    expect($body)->toBe('<p>Pay $100 and $1 fee per C:\\1 file</p>|Party $2');
});
