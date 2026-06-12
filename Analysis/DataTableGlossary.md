# Data Table Variable Glossary

This document describes the three outputs of `getDataTables()` in `Analysis/getDataTables.m`:

```matlab
[DataTable00,DataTable01,DataTable02] = getDataTables()
```

## Overview

- `DataTable00`: one row per participant, combining subject metadata, training-summary measures, probe-summary measures, and response-entropy measures.
- `DataTable01`: one row per training trial, combining subject metadata with training-trial variables. Final output does **not** include `RT`.
- `DataTable02`: starts from the trial-level table before `RT` is removed, then keeps only trials where `Correct == true` and `RT > 200`. This output therefore includes `RT`.

## Shared Subject-Level Variables

These variables appear in `DataTable00` and are also merged into `DataTable01` and `DataTable02`.

### `SubjectId`

Participant identifier. Stored as a categorical variable.

### `ClientTimeZone`

Client/browser timezone reported by the participant's browser. Stored as a categorical variable.

### `Duration_TItrain`

Elapsed time between the recorded start of the training task and the recorded end of the training task. Stored as a MATLAB `duration`.

### `Duration_TIprobe`

Elapsed time between the recorded start of the probe task and the recorded end of the probe task. Stored as a MATLAB `duration`.

### `Age`

Participant age in years at consent, computed as:

```matlab
years(DateTime_Consent - BMY)
```

The raw `BMY` value is reported as birth year and month, then converted to the midpoint of that month before age is computed.

### `zAge`

Z-scored version of `Age`. This is recomputed separately within each output table, so the exact z-scores can differ slightly across `DataTable00`, `DataTable01`, and `DataTable02`.

### `Gender`

Self-reported gender, collapsed to:

- `f`: first character of the submitted text is `f`
- `m`: first character of the submitted text is `m`
- `n`: anything else

### `Handedness`

Self-reported handedness. Expected values are the register-page options:

- `Right`
- `Left`
- `Ambi`

Stored as categorical.

### `L1`

Self-reported first-language code from the registration form, for example `EN`, `DE`, `AR`, `RO`. Stored as categorical.

### `State`

Experiment state/progression code from the registration database. `getData()` filters to `State == 6`, so these tables include only participants who reached the completion state.

### `GroupId`

Stimulus family assigned to the participant. One of:

- `Ani`
- `Art`
- `Fac`
- `Foo`
- `Lin`
- `Obj`
- `Pla`
- `Spa`
- `Tex`

### `A`, `B`, `C`, `D`, `E`, `F`

The six image IDs assigned to latent positions `A` through `F` for that participant.

`MakeAssignment.php` first chooses a `GroupId`, creates six image names such as `Ani0` to `Ani5`, shuffles them, and stores the shuffled result in `A`-`F`.

In task terms, these positions define the latent ordered chain used by the training pairs:

- `A > B`
- `B > C`
- `C > D`
- `D > E`
- `E > F`

So the `A`-`F` columns tell you which concrete image occupied each latent rank position for that participant.

### `dH`

Response-entropy deficit for the training task, defined as:

```matlab
dH = 3 - h
```

where `h` is the entropy of length-3 sequences of left/right responses on training trials where a response was made.

Higher `dH` means lower entropy, i.e. more structured or repetitive response patterns.

### `zH`

Standardized version of the participant's entropy score relative to a null distribution generated from 100,000 random binary response sequences of length 125.

The code computes:

```matlab
zH = (mean(h0) - h) ./ std(h0)
```

Higher `zH` means the participant's response sequence is less random than expected under the null.

### `nTrainR`

Number of training trials on which the participant actually made a response.

## `DataTable00` Variables

`DataTable00` contains one row per participant.

### `nTrainT`

Total number of training trials in the participant's training record. Under the intended design this is usually 125, because there are 5 trained pairs repeated 25 times each.

### `kTrain`

Number of correct training trials.

### `fpCorrect`

Final model-based probability of a correct response in training.

For each trained pair, `getMiniTabs()` fits `dyadicStateSpaceMdl()` to the participant's correctness sequence. It then takes the geometric mean across the 5 trained pairs at the final repetition.

### `afpCorrect`

Chance-corrected rescaling of `fpCorrect`:

```matlab
(fpCorrect - 0.5) * 2
```

This maps:

- `0.5` to `0`
- `1.0` to `1`

### `b0`

Intercept from a logistic regression fit to the training learning curve:

```matlab
mu ~ 1 + x
```

where `x` is repetition index `0:24` and `mu` is the geometric-mean model-based correctness estimate across pairs.

### `b1`

Slope from the same logistic regression learning-curve fit. Larger positive values indicate steeper improvement across repetitions.

### `mRtcTrain`

Mean response time for correct training trials only, in milliseconds.

### `nPremiT`

Number of **premise** probe trials.

Premise probe trials are the directly trained adjacent pairs:

- `A > B`
- `B > C`
- `C > D`
- `D > E`
- `E > F`

The probe task repeats 8 probe pairs twice, so under the intended design there are typically 10 premise probe trials.

### `kPremi`

Number of correct premise probe trials.

### `nInferT`

Number of **inference** probe trials.

Inference probe trials are the untrained non-adjacent pairs:

- `B > D`
- `C > E`
- `B > E`

Under the intended design there are typically 6 inference probe trials.

### `kInfer`

Number of correct inference probe trials.

### `mRtcPremi`

Mean response time for correct premise probe trials only, in milliseconds.

### `mRtcInfer`

Mean response time for correct inference probe trials only, in milliseconds.

## `DataTable01` Variables

`DataTable01` contains one row per **training trial** plus the shared subject-level variables listed above.

In its final saved form, `DataTable01` does **not** include `RT`, because that column is explicitly removed in `getDataTables.m`.

### `t`

Training repetition/block index:

```matlab
floor(((1:numel(TItrainIO))' - 1) ./ 5)
```

This runs from `0` to `24` when all 125 training trials are present. Each value of `t` represents one pass through the 5 trained pairs.

### `PairId`

Identifier for the trained pair shown on that trial, stored as categorical.

Training uses 5 adjacent pairs:

- `0`: `A > B`
- `1`: `B > C`
- `2`: `C > D`
- `3`: `D > E`
- `4`: `E > F`

### `PosOnRight`

Logical flag indicating whether the higher-ranked item on that trial was presented on the right side of the screen.

### `ResponseMade`

Logical flag indicating whether the participant responded before the 4-second trial timeout.

### `pCorrect`

Model-based estimate of the probability of a correct response on that training trial for that participant and pair, derived from `dyadicStateSpaceMdl()`.

### `apCorrect`

Chance-corrected rescaling of `pCorrect`:

```matlab
(pCorrect - 0.5) * 2
```

### `Correct`

Logical accuracy on that trial.

Important note: if a training response is missing and `Correct` is stored as a cell array, `getMiniTabs()` imputes missing `Correct` values at random and sets the corresponding `RT` to `NaN` before fitting the state-space model. That means `Correct` in the derived trial table may include imputed values for non-responses.

## `DataTable02` Variables

`DataTable02` starts from the same trial-level table as `DataTable01`, but it is filtered to:

- `Correct == true`
- `RT > 200`

So `DataTable02` contains only correct training trials with response times above 200 ms.

It contains all of the `DataTable01` trial variables plus `RT`.

### `RT`

Response time in milliseconds from trial onset to the participant's first click.

## Task-Design Notes

### Training task

The training task contains 5 adjacent premise pairs:

- `A > B`
- `B > C`
- `C > D`
- `D > E`
- `E > F`

Each is repeated 25 times for a nominal total of 125 training trials.

### Probe task

The probe task contains 8 total pairs:

- Premise pairs: `A > B`, `B > C`, `C > D`, `D > E`, `E > F`
- Inference pairs: `B > D`, `C > E`, `B > E`

Each probe pair is presented twice, for a nominal total of 16 probe trials.

### Probe missing-response handling

In `getProbeData()`, missing probe responses are handled specially before summary measures are computed:

- missing `Correct` values are replaced with randomly balanced true/false values
- missing `RT` values are set to `NaN`

This affects `kPremi`, `kInfer`, `mRtcPremi`, and `mRtcInfer`.
