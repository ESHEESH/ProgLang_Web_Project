<?php
// chaos.php - all the nonsense logic lives here (requires db.php first, for the session)

const SYMBOLS       = ['🍒', '🍋', '🔔', '💀', '🥔', '7️⃣'];
const JACKPOT_CHANCE = 10;   // percent. The 10% rule. Change ONLY for testing.
const BYPASS_CODE    = 'kalbo';  // secret staff code that skips the slot machine

const TAUNTS = [
    'No jackpot. The house thanks you for your time.',
    'So close! (It was not close.)',
    'Your luck has been reported to management.',
    'Loser. Please retype EVERYTHING and try again.',
    'The slot machine laughed at you. Quietly.',
    'Better luck next time. There will be no luck next time.',
    'Even the potato 🥔 is disappointed in you.',
];

// Makes a "puzzle" and remembers the answer in the session.
// Twist: the answer must be typed BACKWARDS.
function new_puzzle(): string {
    $a  = rand(4, 15);                       // int
    $b  = rand(2, 9);                        // int
    if ($a < $b) { [$a, $b] = [$b, $a]; }   // keep results non-negative
    $op = ['+', '-', '×'][rand(0, 2)];       // random operator

    if ($op === '+')      { $result = $a + $b; }
    elseif ($op === '-')  { $result = $a - $b; }
    else                  { $result = $a * $b; }

    $_SESSION['puzzle_answer'] = strrev((string)$result);   // stored backwards
    return "What is $a $op $b? Type the answer BACKWARDS.";
}

// Checks the puzzle answer. One attempt per puzzle, then it's gone.
function check_puzzle(string $input): bool {
    $expected = $_SESSION['puzzle_answer'] ?? null;
    unset($_SESSION['puzzle_answer']);
    return $expected !== null && $input === $expected;
}

// Spins three reels. 10% chance of a jackpot (7️⃣ 7️⃣ 7️⃣).
function spin_slots(): array {
    $roll = rand(1, 100);

    if ($roll <= JACKPOT_CHANCE) {
        return ['reels' => ['7️⃣', '7️⃣', '7️⃣'], 'jackpot' => true];
    }

    // Losing spin: keep re-rolling until the three reels are NOT all the same
    do {
        $reels = [];
        for ($i = 0; $i < 3; $i++) {
            $reels[] = SYMBOLS[array_rand(SYMBOLS)];
        }
    } while (count(array_unique($reels)) === 1);

    return ['reels' => $reels, 'jackpot' => false];
}

function random_taunt(): string {
    return TAUNTS[array_rand(TAUNTS)];
}
