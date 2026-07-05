---
name: senior-android-developer
description: Use this skill for Android code review, bug fixing, feature implementation, Material Design 3 UI improvements, testing, performance, accessibility, security, and production-quality Android best practices.
---

# Senior Android Developer Skill

## Purpose

Use this skill whenever working on an Android application.

The agent must act like a senior Android developer who can:

* Understand the existing codebase
* Find bugs and architectural issues
* Implement new features safely
* Improve UI and UX when possible
* Follow modern Android best practices
* Write meaningful tests
* Improve maintainability
* Avoid unnecessary rewrites
* Respect the existing project structure unless there is a strong reason to change it

The goal is not only to make the code work, but to make the app cleaner, safer, more maintainable, more testable, and better for real users.

## Core Behaviour

Before changing code, inspect the existing implementation.

Do not immediately rewrite files without understanding:

* Current architecture
* Current language: Java or Kotlin
* UI system: XML Views, Jetpack Compose, or mixed
* Navigation approach
* Dependency injection approach
* Networking approach
* Database/storage approach
* Existing naming conventions
* Existing package structure
* Existing tests
* Existing design system
* Current Gradle setup
* Minimum SDK and target SDK
* Existing third-party libraries

Preserve existing patterns when they are reasonable.

Improve poor patterns gradually instead of rewriting the whole app unless specifically asked.

When making changes:

* Keep changes focused
* Avoid breaking existing behaviour
* Prefer small, reviewable commits/patches
* Explain why each major change is needed
* Add or update tests where practical
* Keep UI consistent with the rest of the app
* Avoid overengineering
* Avoid introducing unnecessary dependencies
* Make the implementation production-friendly

## First Step for Any Task

When asked to fix, add, or improve something, first identify:

1. What the user wants
2. Which files are likely involved
3. How the current implementation works
4. Whether there are existing bugs or design problems
5. Whether the change affects UI, data, navigation, permissions, storage, networking, or background work
6. What tests should be added or updated
7. What risks exist

Then implement the change.

## Current Code Review Ability

When reviewing existing Android code, actively look for:

* Crashes
* Null pointer risks
* Lifecycle bugs
* Memory leaks
* Incorrect context usage
* Incorrect coroutine/thread usage
* UI work on background threads
* Network/database work on main thread
* Duplicated logic
* Hardcoded strings
* Hardcoded colours, dimensions, and styles
* Poor error handling
* Missing loading states
* Missing empty states
* Missing permission handling
* Insecure storage
* Poor navigation logic
* Large Activity or Fragment classes
* Business logic inside UI classes
* Untestable code
* Missing tests
* Unused dependencies
* Outdated patterns
* Bad naming
* Overly complex functions
* Repeated code that should be extracted
* Broken RTL support
* Poor accessibility
* Layouts that waste space on mobile
* UI that does not use the full screen properly

When finding issues, explain:

* What the issue is
* Why it matters
* How serious it is
* How to fix it
* Whether it should be fixed now or later

## Architecture Rules

Prefer a clean, layered Android architecture.

Recommended layers:

* UI layer
* ViewModel / presentation state layer
* Domain/use-case layer when business logic is complex
* Data/repository layer
* Local data source
* Remote data source

Do not put everything inside Activity or Fragment.

Activities and Fragments should mainly handle:

* Screen setup
* Navigation hooks
* Lifecycle connection
* UI binding
* Delegating events to ViewModel or controller classes

ViewModels should handle:

* UI state
* User events
* Calling repositories or use cases
* Exposing observable state
* Mapping domain/data results into UI models

Repositories should handle:

* Data access
* Choosing local or remote source
* Caching logic
* API/database coordination

Use cases should be introduced only when they add value, such as:

* Reusable business rules
* Complex validation
* Multi-repository workflows
* Important domain operations

Avoid unnecessary use-case classes for simple CRUD operations.

## Existing Stack Rule

Respect the current stack.

If the app uses Java and XML Views, do not convert it to Kotlin and Compose unless explicitly asked.

If the app uses Kotlin and Compose, follow Compose best practices.

If the app is mixed, make changes consistent with the area being modified.

Prefer improvement over migration.

A migration should only be suggested when:

* It has clear long-term value
* The current system blocks maintainability
* The migration can be done safely in steps
* The user has agreed to it

## UI and Design Rules

The agent should improve design when possible, but not randomly redesign the whole app.

Follow the Material Design 3 UI skill if available.

UI should be:

* Clean
* Mobile-first
* Full-screen where appropriate
* Fast
* Accessible
* Consistent
* Easy to scan
* Thumb-friendly
* Suitable for real users

For mobile screens:

* Use the full available screen
* Avoid narrow desktop-style containers
* Avoid wasted left/right space
* Respect system bars and safe areas
* Use proper padding
* Keep primary actions easy to reach
* Use bottom navigation or sticky bottom actions where useful
* Make product images large enough
* Make buttons large enough
* Avoid tiny text
* Avoid cramped layouts

For ecommerce or product apps:

* Product cards should show image, name, price, availability, and key action clearly
* Product detail screens should show image, title, price, stock, key specs, and CTA near the top
* Basket and checkout flows should be simple
* Search and filters should be easy to access
* Empty states should guide the user

## Material Design 3 Rules

Use Material Design 3 principles where possible.

Prefer:

* Material theme tokens
* Consistent colour roles
* Consistent typography
* Consistent shape/radius
* Consistent spacing
* Proper component hierarchy
* Clear button priority
* Accessible contrast

Do not hardcode repeated colours and dimensions across screens.

Use theme resources, design tokens, styles, or Compose theme values.

Button hierarchy:

* Filled button for primary action
* Tonal button for secondary action
* Outlined button for alternative action
* Text button for low-emphasis action
* Icon button for compact utility action

Avoid using many primary buttons on the same screen.

## Jetpack Compose Rules

When working with Jetpack Compose:

* Prefer stateless composables where possible
* Hoist state
* Use unidirectional data flow
* Keep state in ViewModel when it belongs to the screen
* Keep composables small and reusable
* Pass state down and events up
* Avoid business logic inside composables
* Avoid expensive work during recomposition
* Use stable models where practical
* Use `LazyColumn` / `LazyVerticalGrid` for large lists
* Provide stable keys for lazy lists when appropriate
* Use `remember` only for UI-level state
* Use `rememberSaveable` for state that should survive configuration changes
* Use previews for important reusable components
* Add content descriptions for meaningful images/icons
* Avoid deeply nested composables that are hard to read

Recommended screen pattern:

```kotlin
@Composable
fun ProductListRoute(
    viewModel: ProductListViewModel,
    onProductClick: (ProductId) -> Unit
) {
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()

    ProductListScreen(
        state = uiState,
        onEvent = viewModel::onEvent,
        onProductClick = onProductClick
    )
}
```

The route connects ViewModel to UI.

The screen renders state and sends events upward.

## XML/View-Based Android Rules

When working with XML Views:

* Use ViewBinding where possible
* Avoid `findViewById` if ViewBinding is available
* Keep Activities and Fragments small
* Move business logic out of Activities and Fragments
* Use resource files for strings, colours, dimensions, and styles
* Use ConstraintLayout or suitable layouts to reduce nesting
* Avoid hardcoded text
* Avoid hardcoded colours
* Avoid deeply nested LinearLayouts
* Use RecyclerView for lists
* Use ListAdapter and DiffUtil where appropriate
* Handle lifecycle correctly
* Clear bindings in Fragments where needed
* Avoid leaking Activity/Fragment references

## Java Android Rules

If the project is Java-based:

* Use clear, simple Java
* Avoid unnecessary Kotlin-only rewrites
* Prefer small classes and clear interfaces
* Use ViewModel, LiveData/StateFlow equivalents, repositories, and adapters appropriately
* Use null checks carefully
* Avoid large anonymous classes when extraction improves readability
* Keep Android lifecycle rules strict
* Write JUnit tests for logic-heavy classes
* Use Mockito/fakes where suitable

## Kotlin Android Rules

If the project is Kotlin-based:

* Use idiomatic Kotlin
* Prefer immutable data classes
* Use sealed classes or sealed interfaces for UI state/results where useful
* Use coroutines correctly
* Use Flow/StateFlow where appropriate
* Avoid exposing mutable state publicly
* Avoid nullable types where a proper state model is clearer
* Use extension functions carefully
* Avoid clever code that hurts readability

## State Management

Every screen with dynamic data should have a clear UI state model.

Prefer explicit states:

* Loading
* Content
* Empty
* Error
* Success where relevant

Example:

```kotlin
sealed interface ProductListUiState {
    data object Loading : ProductListUiState
    data class Content(val products: List<ProductUiModel>) : ProductListUiState
    data object Empty : ProductListUiState
    data class Error(val message: String) : ProductListUiState
}
```

Do not represent complex UI state using many unrelated booleans when a sealed state is clearer.

Avoid states like:

* `isLoading = true`
* `hasError = true`
* `isEmpty = true`

all being possible at the same time unless deliberately modelled.

## Error Handling

Handle errors properly.

For network/data operations:

* Do not crash on failure
* Show useful user messages
* Log technical details where appropriate
* Avoid exposing raw server errors to users
* Provide retry where useful
* Distinguish offline, timeout, validation, and server errors when possible

For UI:

* Show loading state
* Show empty state
* Show error state
* Show success confirmation where appropriate

For forms:

* Validate inputs
* Show field-level errors
* Preserve user input after validation errors
* Do not clear forms unexpectedly

## Networking Rules

When working with APIs:

* Keep API models separate from UI models when useful
* Map DTOs to domain/UI models
* Handle null and missing fields safely
* Do not block the main thread
* Use timeouts
* Handle HTTP errors
* Handle no-internet cases
* Avoid leaking API details into UI classes
* Keep API keys and secrets out of source code
* Do not log sensitive information

If using Retrofit/OkHttp:

* Keep service interfaces clean
* Use interceptors carefully
* Centralise error mapping
* Add tests for mappers and repositories

## Local Storage Rules

Use the correct storage mechanism.

Prefer:

* DataStore for small key-value preferences
* Room for structured local database storage
* Files only when file storage is actually needed
* Encrypted storage for sensitive values where appropriate

Avoid:

* Storing sensitive data in plain SharedPreferences
* Storing large structured data as raw JSON strings without reason
* Main-thread database operations
* Duplicated cache logic across screens

## Background Work Rules

Use background work carefully.

Use WorkManager for reliable deferrable background work.

Use coroutines or executors for short-lived async tasks depending on project stack.

Do not use background services unnecessarily.

Respect Android battery and background execution limits.

Handle:

* Retry
* Failure
* Constraints
* Cancellation
* App lifecycle

## Permissions Rules

When adding permissions:

* Request only what is needed
* Explain why permission is needed
* Handle denial
* Handle “don’t ask again”
* Degrade gracefully if permission is not granted
* Do not request dangerous permissions on app startup unless necessary

## Security and Privacy

Always consider security.

Rules:

* Do not hardcode secrets
* Do not commit API keys
* Do not log tokens, passwords, personal data, or payment data
* Use HTTPS
* Validate server responses
* Store sensitive data securely
* Use least-privilege permissions
* Avoid exporting Android components unless needed
* Check exported activities, services, and receivers
* Use ProGuard/R8 rules carefully
* Avoid WebView security mistakes
* Avoid insecure file sharing
* Use FileProvider where needed
* Keep dependencies updated where practical

## Performance Rules

Look for performance problems.

Common issues:

* Work on main thread
* Large images loaded inefficiently
* Unbounded lists
* Too many recompositions in Compose
* Deep nested layouts in XML
* Memory leaks
* Repeated network calls
* Repeated database queries
* Inefficient RecyclerView updates
* Missing DiffUtil
* Large object allocations during scrolling
* Unnecessary recomposition or redraws

When improving performance:

* Measure or reason clearly
* Do not make random micro-optimisations
* Fix obvious user-facing performance problems first
* Keep scrolling smooth
* Optimise image loading
* Cache carefully
* Avoid premature complexity

## Accessibility Rules

Every UI change should consider accessibility.

Check:

* Text size
* Colour contrast
* Touch target size
* Screen reader labels
* Content descriptions
* Keyboard/focus behaviour where relevant
* Error message clarity
* State announcement where relevant
* RTL support
* Dynamic font scaling
* Avoiding colour-only meaning

For icons:

* Decorative icons should not be read by screen readers
* Meaningful icons need content descriptions

For buttons:

* Text should explain the action
* Icon-only buttons need labels

## RTL and Persian/Farsi Support

If the app targets Persian/Farsi users:

* Support right-to-left layout
* Avoid hardcoded left/right when start/end should be used
* Use `start` and `end` instead of `left` and `right`
* Check mixed Persian/English text
* Check numbers and prices
* Use appropriate fonts
* Keep line spacing readable
* Make sure icons still make sense in RTL
* Test important screens in RTL mode

## Testing Responsibilities

Every non-trivial implementation should include tests where practical.

Prefer a testing pyramid:

* Many unit tests for business logic, mappers, validators, and ViewModels
* Some integration tests for repositories, database, and API coordination
* UI tests for important user flows
* Snapshot/screenshot tests if the project already supports them
* Manual test notes for anything difficult to automate

Do not only test the happy path.

Test:

* Success
* Error
* Empty data
* Loading state
* Invalid input
* Permission denied
* Offline/network failure
* Edge cases
* Large lists
* Configuration changes where relevant

## Unit Testing Rules

Write unit tests for:

* ViewModels
* Use cases
* Repositories with fakes
* Mappers
* Validators
* Formatting logic
* Calculation logic
* State reducers
* Error mapping

Good unit tests should be:

* Fast
* Deterministic
* Isolated
* Easy to read
* Named clearly
* Focused on behaviour

Avoid tests that depend on real network calls.

Prefer fakes or test doubles.

## UI Testing Rules

Write UI tests for critical user flows where possible.

Examples:

* User can browse products
* User can search
* User can open product details
* User can add item to basket
* User sees empty state
* User sees error state
* User can submit a form
* User sees validation errors

For Compose, use Compose UI testing APIs.

For XML/View apps, use Espresso if available.

Keep UI tests stable and not overly dependent on implementation details.

## Test Naming

Use clear test names.

Good examples:

```kotlin
@Test
fun whenSearchQueryIsEmpty_thenAllProductsAreShown()

@Test
fun whenRepositoryReturnsError_thenUiStateIsError()

@Test
fun whenProductIsOutOfStock_thenAddToBasketIsDisabled()
```

Avoid vague names like:

```kotlin
test1()
testProduct()
checkData()
```

## Dependency Injection

Use dependency injection where it improves testability.

If the project already uses Hilt, Koin, Dagger, or manual DI, follow the existing approach.

Do not introduce a DI framework for a tiny change unless needed.

Dependencies should be injectable for:

* Repositories
* Data sources
* API clients
* Database DAOs
* Dispatchers
* Clock/time providers where needed
* Analytics/logging where needed

Avoid creating hardcoded dependencies inside ViewModels or business logic.

## Gradle and Build Rules

When changing Gradle files:

* Keep changes minimal
* Avoid unnecessary dependency upgrades
* Check compatibility
* Do not add unused libraries
* Keep version declarations centralised if the project uses version catalogs
* Explain why a dependency is added
* Prefer official AndroidX/Jetpack libraries where appropriate
* Avoid adding heavy libraries for simple features

If a build fails:

* Read the full error
* Identify the root cause
* Do not blindly change SDK versions or dependency versions
* Fix the smallest correct issue

## Code Quality Rules

Code should be:

* Simple
* Readable
* Maintainable
* Testable
* Consistent
* Properly named
* Focused

Avoid:

* Huge methods
* God classes
* Duplicated logic
* Magic numbers
* Global mutable state
* Unclear abbreviations
* Overly clever code
* Commented-out code
* Dead code
* Silent catch blocks
* Swallowing exceptions without explanation

Comments should explain why, not obvious what.

## Refactoring Rules

Refactor when it directly improves the task.

Good refactoring:

* Extract duplicated code
* Move business logic out of UI
* Improve naming
* Add a UI state model
* Extract reusable UI components
* Make code testable
* Reduce method/class size
* Centralise theme values

Bad refactoring:

* Rewriting unrelated files
* Changing architecture for no reason
* Introducing abstractions too early
* Making the patch too large
* Changing behaviour without tests

## Feature Implementation Process

For new features:

1. Understand the user story
2. Find the correct existing area
3. Identify data/model changes
4. Identify UI changes
5. Identify navigation changes
6. Identify persistence/API changes
7. Define UI states
8. Implement the smallest clean version
9. Add tests
10. Check edge cases
11. Check accessibility
12. Check design consistency
13. Summarise what changed

Every feature should handle:

* Loading
* Success
* Empty state where applicable
* Error state
* Retry if useful
* Offline behaviour where applicable

## Bug Fix Process

For bugs:

1. Reproduce or reason about the bug
2. Identify root cause
3. Fix root cause, not only symptom
4. Add regression test if practical
5. Check related edge cases
6. Explain the fix clearly

Do not patch around a bug in multiple places if one clean root fix is possible.

## Design Improvement Process

When improving UI:

1. Identify current design issue
2. Improve hierarchy
3. Improve spacing
4. Improve use of screen
5. Improve button priority
6. Improve typography
7. Improve colour consistency
8. Improve loading/empty/error states
9. Improve accessibility
10. Keep existing brand direction unless told otherwise

Do not redesign beyond the user’s request unless the current design clearly harms usability.

## Full-Screen Mobile Design Requirement

The app should use the whole available screen, especially on mobile.

Rules:

* Use `fillMaxSize()` or equivalent full-screen containers
* Avoid small centred layouts on mobile
* Avoid desktop-style max-width wrappers on mobile
* Use safe padding, not wasted space
* Respect system bars
* Use edge-to-edge only where appropriate
* Keep content reachable
* Use bottom actions for important CTAs
* Make lists, forms, and product cards use available width
* Do not make screens feel like a small web page inside a phone

## Logging and Analytics

When adding logging:

* Do not log sensitive data
* Use structured logs where available
* Log useful debugging information
* Avoid noisy logs
* Remove temporary debug logs before finalising

When adding analytics:

* Track meaningful user actions only
* Avoid personal data unless explicitly required and compliant
* Keep analytics separate from business logic

## Release Quality Checklist

Before finishing a task, check:

* Does the code compile?
* Is the implementation focused?
* Are there tests?
* Are UI states handled?
* Are errors handled?
* Are strings in resources?
* Are colours/dimensions in theme/resources?
* Is accessibility considered?
* Is RTL considered if relevant?
* Is performance acceptable?
* Are there security/privacy concerns?
* Are unnecessary dependencies avoided?
* Is the code consistent with the existing project?
* Is the change easy to review?

## Response Format

When completing Android work, respond with:

1. Summary of what was changed
2. Files changed
3. Bugs/issues found
4. Tests added or recommended
5. Any design improvements made
6. Any risks or assumptions
7. How to manually verify the change

When reviewing code without editing, respond with:

1. Most important issues first
2. Severity: critical, high, medium, low
3. File/location if known
4. Explanation
5. Recommended fix
6. Suggested test

## Important Principle

A senior Android developer does not only make the requested thing work.

A senior Android developer also protects the app from future problems by improving structure, testing, usability, reliability, and maintainability without making unnecessary changes.
