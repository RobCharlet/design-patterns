# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a command-line RPG battle game built with Symfony 6.1 for the SymfonyCasts Design Patterns tutorial. Characters battle each other in turn-based combat with stats, stamina management, and dice rolls determining outcomes.

## Running the Application

**Install dependencies:**
```bash
composer install
```

**Run the game:**
```bash
php bin/console app:game:play
```

The app is CLI-only with no web server needed.

**Clear cache:**
```bash
php bin/console cache:clear
```

## Architecture

### Core Game Flow

The game follows a simple turn-based battle system:

1. **GameCommand** (`src/Command/GameCommand.php`) - Entry point console command that:
   - Prompts player to select a character type
   - Randomly selects an AI opponent
   - Orchestrates the battle flow
   - Displays results and prompts for replay

2. **GameApplication** (`src/GameApplication.php`) - Main game logic coordinator:
   - Creates characters based on type selection (fighter/mage/archer)
   - Runs the battle loop with alternating player/AI attacks
   - Determines winner/loser and tracks combat metrics
   - Character factory uses match expression with predefined stats per type

3. **Character** (`src/Character/Character.php`) - Combat entity with:
   - Health and stamina systems (stamina depletes with attacks, causes exhausted turns when empty)
   - Attack logic using base damage + dice rolls
   - Armor-based damage reduction on incoming attacks
   - `rest()` method restores health/stamina between fights

4. **FightResult** (`src/FightResult.php`) - Data container tracking:
   - Winner/loser references
   - Combat statistics (rounds, damage dealt/received, exhausted turns)
   - Used for post-battle display

5. **Dice** (`src/Dice.php`) - Static utility for random number generation using `random_int()`

### Character Types

Characters are created with distinct stat profiles (maxHealth, baseDamage, armor):
- **Fighter**: 90 HP, 12 base damage, 25% armor
- **Archer**: 80 HP, 10 base damage, 15% armor
- **Mage**: 70 HP, 8 base damage, 10% armor

### Battle Mechanics

- Attacks cost 25-45 stamina (25 + 1d20 roll)
- Attack damage is base damage + 1d6 roll
- When stamina drops to 0 or below, character is exhausted (misses turn, stamina resets to 100)
- Armor reduces incoming damage by a percentage
- Battle continues until one character reaches 0 HP

### Dependency Injection

Standard Symfony autowiring is configured in `config/services.yaml`. All classes in `src/` are auto-registered as services. The `GameCommand` receives `GameApplication` via constructor injection.

## File Structure

```
src/
├── Character/Character.php    # Combat entity with stats/mechanics
├── Command/GameCommand.php    # CLI command orchestrating gameplay
├── GameApplication.php        # Core game logic and character factory
├── FightResult.php           # Battle statistics container
├── Dice.php                  # Dice rolling utility
└── Kernel.php                # Symfony kernel
```

## Development Notes

- PHP 8.1+ required
- Uses PHP 8 features: constructor property promotion, match expressions, attributes
- No database or persistence layer - each battle is isolated
- The `tutorial/` directory contains reference code for the SymfonyCasts course

## Available MCP Servers

This project has access to the following MCP (Model Context Protocol) servers:

### GitHub MCP (`mcp__github__*`)
Use GitHub MCP tools for:
- Creating and managing branches
- Creating, updating, and reviewing pull requests
- Managing issues and comments
- Checking CI/CD status
- Searching repositories and code

**Key tools:**
- `mcp__github__create_or_update_file` - Create/update files via GitHub API
- `mcp__github__push_files` - Push multiple files in a single commit
- `mcp__github__create_pull_request` - Create PRs programmatically
- `mcp__github__get_file_contents` - Read files from GitHub
- `mcp__github__create_branch` - Create new branches
- `mcp__github__list_commits` - View commit history

**When to use:** Prefer GitHub MCP tools when working with Git operations that require PR creation, branch management, or GitHub-specific features. This ensures proper workflow adherence.

### Context7 MCP (`mcp__context7__*`)
Use Context7 MCP for enhanced codebase understanding:
- Semantic code search across the project
- Finding related code patterns
- Understanding code relationships and dependencies
- Analyzing code structure and architecture

**Key tools:**
- `mcp__context7__semantic_search` - Search code by meaning, not just text
- `mcp__context7__find_references` - Find all references to a symbol
- `mcp__context7__analyze_codebase` - Get architectural insights

**When to use:** Use Context7 when you need to understand code relationships, find similar patterns, or perform semantic analysis beyond simple text search.

## Git Workflow Rules

### Branch Management

**CRITICAL: NEVER commit directly to main branch**

Always create a feature/fix/chore branch for any changes.

**Branch naming conventions:**
- `feature/description` - for new features
- `fix/description` - for bug fixes
- `chore/description` - for maintenance tasks (dependencies, config, etc.)

Examples: `fix/github-actions-sqlite-path`, `feature/add-dinosaur-feeding`

### Pull Request Workflow

1. Create a branch for your changes
2. Make commits on the branch
3. Push the branch to GitHub
4. Create a Pull Request to main
5. Wait for CI checks to pass
6. Merge the PR (squash merge preferred)

### Required for All Changes

- All changes MUST go through Pull Requests
- PRs MUST have passing CI checks before merge
- Use descriptive PR titles and detailed descriptions
- Include test plan in PR description

### Commit Message Format

- Use clear, descriptive commit messages
- Start with a verb in imperative mood (Add, Fix, Update, Remove)
- Include AI co-author attribution: `Co-Authored-By: Claude <noreply@anthropic.com>`
- Add emoji prefix when using Claude Code: `🤖 Generated with [Claude Code](https://claude.com/claude-code)`

### Branch Protection

The main branch is protected:
- No force pushes allowed
- No direct commits allowed
- Requires pull request reviews (when configured)
