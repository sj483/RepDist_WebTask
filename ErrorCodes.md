# Participant-Facing Error Codes

This file lists every custom error code that is intentionally surfaced to
participants in this repository. It covers codes shown in browser `alert`
dialogs, codes shown on `Error.html`, and codes that can reach
`Error.html` through the fallback completion link.

It does not attempt to catalogue raw server-side `die(...)` messages,
because those are not part of the custom participant-facing code scheme.

| Code | Where it can be thrown | What it relates to | Display to participant |
| --- | --- | --- | --- |
| `000` | `index.html` in `LogLanding()` | Landing flow was asked to continue without a usable `PoolId` or `SubjectId`. | `Error.html` |
| `001` | `index.html` in `SubmitForm()` | Sign-in flow could not continue after a participant entered a pool-style ID that requires subject generation (`Prolific`, `SONA`, or Sussex-style ID). | `Error.html` |
| `002` | `index.html` in `SubmitForm()` | Returning-participant sign-in could not continue after an 8-character `SubjectId` was entered. | `Error.html` |
| `003` | `index.html` in `Init()` | Direct-entry resume flow with a `SubjectId` in the URL could not be logged or redirected. | `Error.html` |
| `004` | `index.html` in `Init()` | Direct-entry resume flow with a `PoolId` in the URL could not generate/log a `SubjectId` or redirect. | `Error.html` |
| `005` | `LogUnfocus.js` in `LogDeltaVisibility()` | The task pages failed while logging a tab-focus change (`LogUnfocus.php` or `LogRefocus.php`). | `alert` |
| `006` | `Coventry.html` in `GetState()` | Coventry page could not recover the participant's exclusion state. | `alert` |
| `010` | `Register.html` in `SubmitForm()` | Registration submission failed or did not return a usable next-step URL. | `Error.html` |
| `020` | `Instruct.html` in `OnBodyLoad()` | Instruction page was opened with an unexpected `TaskId`. | `Error.html` |
| `021` | `Instruct.html` in `Continue()` | Instruction-completion logging returned without a usable redirect target. | `Error.html` |
| `022` | `Instruct.html` in `Continue()` | Instruction-completion request failed before a usable response was received. | `Error.html` |
| `030` | `FunctionSpec.js` in `OnFinishTask()` | TI training/probe task finished, but writing task data or retrieving the next URL failed. | `Error.html` |
| `040` | `Complete.html` in `OnBodyLoad()`; fallback link from `GetCompletionLink.php` | Completion page could not find any `Register` record for the supplied `SubjectId`. | `alert` then `Error.html`; fallback link can open `Error.html` directly |
| `041` | `Complete.html` in `OnBodyLoad()`; fallback link from `GetCompletionLink.php` | Completion page was reached before the participant had completed the full experiment (`State != 6`). | `alert` then `Error.html`; fallback link can open `Error.html` directly |

## Grouping

The numbering is intentionally grouped by participant progress:

- `000` to `006`: landing, routing, and exclusion-related failures
- `010`: registration
- `020` to `022`: instruction pages
- `030`: task completion/writeback
- `040` to `041`: final completion page
