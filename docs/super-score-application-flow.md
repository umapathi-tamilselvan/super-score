# 🏏 Super Score — Complete Application Flow

## 1. Application Overview

Super Score is a cricket scoring and statistics application.

The complete user journey starts from registration/login and continues through team and player management, match setup, toss, ball-by-ball scoring, innings management, match completion, scorecard, statistics, sharing, and match history.

---

# 2. Complete Application Flow

```text
                    SUPER SCORE
                         │
                         ▼
                  Splash Screen
                         │
                         ▼
              ┌─────────────────────┐
              │ Authentication      │
              ├─────────────────────┤
              │ Login               │
              │ Register            │
              │ Forgot Password     │
              └─────────────────────┘
                         │
                         ▼
                  User Onboarding
                         │
                         ▼
                   Home Dashboard
                         │
        ┌────────────────┼─────────────────┐
        ▼                ▼                 ▼
      Teams           Players           Matches
        │                                  │
        └──────────────┬───────────────────┘
                       ▼
                 Create Match
                       │
                       ▼
                Match Configuration
                       │
                       ▼
                 Select Playing XI
                       │
                       ▼
                      Toss
                       │
                       ▼
                 Start Match
                       │
                       ▼
                1st Innings
                       │
                       ▼
               Ball-by-Ball Scoring
                       │
                       ▼
                Innings Complete
                       │
                       ▼
                 2nd Innings
                       │
                       ▼
               Ball-by-Ball Scoring
                       │
                       ▼
                  Match Complete
                       │
                       ▼
                 Match Result
                       │
             ┌─────────┼──────────┐
             ▼         ▼          ▼
          Scorecard  Statistics  Share
                       │
                       ▼
                 Match History
```

---

# 3. Splash Screen

When the application opens:

```text
┌─────────────────────────┐
│                         │
│          🏏             │
│                         │
│      SUPER SCORE        │
│                         │
│   Cricket Scoring       │
│                         │
└─────────────────────────┘
```

The application checks:

- Whether the user is logged in.
- Whether the session is valid.
- Whether the user has an unfinished live match.
- Whether the application can connect to the backend.

### Routing

```text
Not Logged In
      ↓
Login / Register

Logged In
      ↓
Dashboard

Unfinished Match
      ↓
Resume Match
```

---

# 4. Authentication

## 4.1 Registration

User selects **Create Account**.

### Registration Fields

```text
Create Account

Full Name
[________________]

Email
[________________]

Mobile Number
[________________]

Password
[________________]

Confirm Password
[________________]

☐ I agree to Terms & Conditions

        [ REGISTER ]
```

### Validation

- Full name is required.
- Valid email is required.
- Valid mobile number is required.
- Password must satisfy the configured password rules.
- Password and confirmation must match.
- Terms and Conditions must be accepted.

After successful registration:

```text
Registration Successful
        ↓
Email / OTP Verification
```

---

# 5. OTP Verification

If OTP verification is enabled:

```text
Verify Account

OTP sent to
+91 XXXXX XXXXX

[ _ ] [ _ ] [ _ ] [ _ ] [ _ ] [ _ ]

Didn't receive OTP?

Resend OTP

[ VERIFY ]
```

After successful verification:

```text
OTP Verified
     ↓
Profile Setup
```

---

# 6. Profile Setup

First-time users complete their profile.

```text
Complete Your Profile

Profile Photo

Full Name
Email
Mobile

Preferred Role

○ Player
○ Scorer
○ Team Manager
○ Organizer
○ All

[ CONTINUE ]
```

The user can later update profile information.

---

# 7. Home Dashboard

After login:

```text
┌────────────────────────────────┐
│ Hi, User 👋                    │
│                                │
│ + START NEW MATCH              │
│                                │
│ 🔴 LIVE MATCHES                │
│                                │
│ 📅 UPCOMING MATCHES            │
│                                │
│ RECENT MATCHES                 │
│                                │
│ ─────────────────────────────  │
│ Teams | Players | Statistics   │
└────────────────────────────────┘
```

### Main Navigation

```text
Home
Matches
Teams
Players
Profile
```

---

# 8. Team Management

Users can create and manage cricket teams.

## 8.1 Team List

```text
Teams

+ Create Team

Chennai Warriors
Tamil Kings
Coimbatore CC
```

## 8.2 Create Team

```text
Create Team

Team Name
[________________]

Short Name
[________]

Team Logo
[ Upload ]

City / Location
[________]

[ CREATE TEAM ]
```

## 8.3 Team Management

A team can contain:

- Team name
- Short name
- Logo
- Location
- Players
- Captain
- Vice Captain
- Wicket Keeper
- Team history

---

# 9. Player Management

Players can be created and managed independently and assigned to teams.

## 9.1 Player List

```text
Chennai Warriors

Players

+ Add Player

Arun
Kumar
Ravi
Suresh
Mani
...
```

## 9.2 Add Player

```text
Add Player

Name
Date of Birth
Photo

Role

○ Batter
○ Bowler
○ All Rounder
○ Wicket Keeper

Batting Style
Bowling Style

[ SAVE ]
```

Player information can later be reused across multiple matches.

---

# 10. Create New Match

From the Dashboard or Matches section:

**Start New Match**

```text
Create Match

Match Name
[ Chennai Warriors vs Tamil Kings ]

Format

○ T10
○ T20
○ ODI
○ Custom

Overs
[ 20 ]

Date
[ DD-MM-YYYY ]

Time
[ HH:MM ]

Venue
[________________]

[ NEXT ]
```

### Match Configuration

The match can contain:

- Match name
- Match format
- Number of overs
- Date
- Time
- Venue
- Match rules
- Optional tournament association

---

# 11. Select Teams

```text
SELECT TEAMS

Team A
[ Chennai Warriors ▼ ]

VS

Team B
[ Tamil Kings ▼ ]

[ NEXT ]
```

### Validation

- Both teams are required.
- Team A and Team B cannot be the same.
- Both teams must have enough registered players for the selected format.

---

# 12. Select Playing XI

Playing XI is selected separately for each team.

## Team A

```text
Chennai Warriors

Select Playing XI

☑ Arun
☑ Kumar
☑ Ravi
☑ Suresh
☑ Mani
☑ Bala
☑ Vijay
☑ Ajay
☑ Prakash
☑ Santhosh
☑ Raj

Captain
[ Arun ▼ ]

Wicket Keeper
[ Kumar ▼ ]

[ NEXT ]
```

Then the same process is completed for Team B.

### Playing XI Data

The system stores:

- Selected players
- Captain
- Vice captain, if applicable
- Wicket keeper
- Substitute players
- Player roles

---

# 13. Toss

```text
                  TOSS

Chennai Warriors
       VS
Tamil Kings

Toss Winner

[ Chennai Warriors ▼ ]

Decision

[ BAT ]       [ BOWL ]

[ START MATCH ]
```

The system stores:

```text
toss_winner
toss_decision
batting_first
bowling_first
```

---

# 14. Match Start

Before the first delivery:

```text
FIRST INNINGS

Chennai Warriors

Opening Batter
[ Arun ]

Non-Striker
[ Kumar ]

Opening Bowler
[ Ravi ]

Overs: 0 / 20

[ START INNINGS ]
```

The selected opening players become the initial striker, non-striker, and bowler.

---

# 15. Live Ball-by-Ball Scoring

The live scoring screen is the most important screen in the application.

```text
┌──────────────────────────────┐
│ CHENNAI WARRIORS             │
│ 42/1       5.3 Overs         │
├──────────────────────────────┤
│ Arun         22 (17)         │
│ Kumar        10 (12)         │
│                              │
│ Bowler: Ravi                 │
│ 2.3 - 0 - 15 - 1             │
├──────────────────────────────┤
│                              │
│  0      1      2      3      │
│                              │
│  4      5      6             │
│                              │
│ WIDE   NO BALL  BYE          │
│ LEG BYE     WICKET           │
├──────────────────────────────┤
│ Over                         │
│ 1  4  0  W  2  1            │
├──────────────────────────────┤
│ UNDO              MORE       │
└──────────────────────────────┘
```

---

# 16. Delivery Processing

For every delivery:

```text
Ball
 ↓
Identify Striker
 ↓
Identify Non-Striker
 ↓
Identify Bowler
 ↓
Record Runs
 ↓
Record Extras
 ↓
Check Wicket
 ↓
Check Legal Delivery
 ↓
Update Team Score
 ↓
Update Batter Statistics
 ↓
Update Bowler Statistics
 ↓
Update Partnership
 ↓
Update Over
 ↓
Check Innings Conditions
```

---

# 17. Run Scoring

The scorer can record:

```text
0
1
2
3
4
5
6
```

The system automatically updates:

- Team score
- Batter score
- Batter balls faced
- Boundary count
- Bowler runs conceded where applicable
- Legal delivery count
- Strike rotation

---

# 18. Extras

The scorer can select:

```text
EXTRAS

Wide
No Ball
Bye
Leg Bye
Penalty

[ CANCEL ]
```

## Wide

```text
Wide

Runs:
[ 1 ] [ 2 ] [ 3 ] [ 4 ]

[ CONFIRM ]
```

The system must treat the delivery as a non-legal delivery.

## No Ball

```text
No Ball

Runs from Bat:
0 1 2 3 4 6

Additional Runs:
[...]

Free Hit:
Yes / No

[ CONFIRM ]
```

The system handles the legal delivery count and free-hit state according to the configured match rules.

---

# 19. Wicket

When the scorer selects **WICKET**:

```text
WICKET

Dismissal Type

○ Bowled
○ Caught
○ LBW
○ Run Out
○ Stumped
○ Hit Wicket
○ Retired Hurt
○ Retired Out
○ Obstructing the Field

[ CONTINUE ]
```

## Caught

If the dismissal is Caught:

```text
Fielder

[ Select Fielder ▼ ]

[ CONFIRM WICKET ]
```

## New Batter

After the wicket:

```text
Arun OUT

34 (27)

Select New Batter

[ Kumar ▼ ]

[ CONFIRM ]
```

The system updates:

- Wicket count
- Batter dismissal
- Bowler wicket where applicable
- Fall of wicket
- New batter
- Strike state
- Scorecard

---

# 20. End of Over

After six legal deliveries:

```text
OVER COMPLETE

10.0 Overs

Score
78/2

This Over

1  4  0  2  W  1

Current Batter
Kumar

Non-Striker
Suresh

Select Next Bowler

[ Ravi ▼ ]

[ NEXT OVER ]
```

The system automatically manages:

- Over number
- Legal ball count
- Strike rotation
- Bowler figures
- Over summary
- Run rate

---

# 21. Pause and Resume Match

The scorer can pause the match.

```text
MORE

Pause Match
Match Info
Scorecard
Ball History
Edit Ball
Retire Batter
Change Bowler
Settings
End Innings
```

If the application is closed during an active match:

```text
SUPER SCORE

You have an ongoing match.

Chennai Warriors
78/2
10.0 Overs

[ RESUME MATCH ]
```

The match should resume from the last successfully saved state.

---

# 22. Ball History and Correction

Scoring mistakes must be correctable.

```text
Ball History

10.1   1 run
10.2   4 runs
10.3   Wide
10.4   Wicket
```

Selecting a delivery opens the edit screen:

```text
Edit Ball

Current:
Wide

Change to:

0
1
2
4
Wide
No Ball
Wicket

[ SAVE ]
```

After editing a delivery, the application must recalculate all affected values.

Affected data can include:

- Team score
- Batter score
- Batter balls
- Bowler figures
- Extras
- Wickets
- Over number
- Strike
- Partnership
- Fall of wicket
- Match result if applicable

---

# 23. First Innings Completion

The first innings can end when:

- All wickets have fallen.
- Allocated overs are completed.
- The innings is manually ended according to match rules.
- The match is stopped or abandoned.
- Other configured innings-ending conditions occur.

Example:

```text
INNINGS COMPLETE

Chennai Warriors
156/8

20 Overs

Target
157
```

---

# 24. Innings Summary

```text
FIRST INNINGS

Chennai Warriors
156/8
20 Overs

Run Rate
7.80

Top Scorer
Arun - 64

Best Bowler
Ravi - 3/24

Extras
8

[ VIEW SCORECARD ]

[ START 2ND INNINGS ]
```

---

# 25. Second Innings Setup

```text
SECOND INNINGS

Tamil Kings

Target: 157

Opening Batter
[ Player ]

Non-Striker
[ Player ]

Opening Bowler
[ Player ]

[ START INNINGS ]
```

The target is automatically calculated from the first innings score and match rules.

---

# 26. Second Innings / Chase

During the second innings:

```text
TAMIL KINGS

124/5
17.2 Overs

TARGET       157
REQUIRED      33
BALLS LEFT    16

RRR           12.38
CRR            7.15
```

Continue the same ball-by-ball scoring process.

The system continuously calculates:

- Current score
- Required runs
- Remaining balls
- Current run rate
- Required run rate
- Wickets remaining
- Partnership
- Batter statistics
- Bowler statistics

---

# 27. Match Completion

A match can finish through different outcomes.

## Target Achieved

```text
TARGET ACHIEVED 🎉

Tamil Kings
157/7

18.4 Overs

Tamil Kings won by 3 wickets
```

## Overs Completed

```text
INNINGS COMPLETE

Tamil Kings
145/8

20 Overs

Chennai Warriors won by 11 runs
```

## Tie

```text
MATCH TIED

Both teams
156 runs
```

## Super Over

For formats that support it:

```text
MATCH TIED
     ↓
SUPER OVER
     ↓
Team A Super Over
     ↓
Team B Super Over
     ↓
Winner
```

---

# 28. Player of the Match

After the match:

```text
PLAYER OF THE MATCH

Suggested Players

Arun
64 runs

Ravi
3 wickets

Kumar
48 runs

[ SELECT PLAYER ]

[ CONFIRM ]
```

The system can later provide automatic suggestions based on match performance, while allowing the organizer/scorer to make the final selection.

---

# 29. Final Match Result

```text
🏆 MATCH RESULT

Tamil Kings won by 3 wickets

Chennai Warriors
156/8
20 Overs

Tamil Kings
157/7
18.4 Overs

Player of the Match
Arun

────────────────

[ SCORECARD ]

[ BALL BY BALL ]

[ STATISTICS ]

[ SHARE ]
```

---

# 30. Final Scorecard

## Batting

```text
Chennai Warriors

BATTER       R    B    4s   6s   SR

Arun         64   42   7    2   152.38
Kumar        28   25   3    0   112.00
Suresh       18   15   2    1   120.00
```

## Bowling

```text
Tamil Kings

BOWLER       O     R    W    ECO

Ravi         4     24   3    6.00
Mani         4     31   1    7.75
```

## Additional Scorecard Information

- Extras
- Fall of Wickets
- Partnerships
- Over Summary
- Ball-by-Ball Commentary
- Match Result
- Player of the Match

---

# 31. Match Sharing

After the match:

```text
MATCH COMPLETE

[ Share Scorecard ]

        ↓

WhatsApp
Instagram
Copy Link
Share
```

The application can generate a public match URL:

```text
https://superscore.app/match/ABC123
```

Public users can view:

- Live score, if the match is live.
- Final score.
- Scorecard.
- Ball-by-ball information.
- Match result.
- Player statistics.

Public viewers must not be able to modify the match.

---

# 32. Match History

Completed matches are available in Match History.

```text
My Matches

Completed

Chennai Warriors
156/8
vs
Tamil Kings
157/7

Tamil Kings won
19 Sep 2026

────────────────

Coimbatore CC
142/6
vs
Madurai CC
139/9

Coimbatore CC won
15 Sep 2026
```

### Filters

- Date
- Team
- Tournament
- Match format
- Result
- Venue

---

# 33. Player Statistics

Every completed match contributes to player statistics.

```text
PLAYER

Arun

Matches       25
Innings       24
Runs          906
Highest       92
Average       39.39
Strike Rate   141.2

50s           8
100s          0
4s            78
6s            34
```

### Bowling Statistics

```text
Bowling

Matches
Overs
Runs
Wickets
Economy
Average
Best Bowling
4 Wickets
5 Wickets
```

---

# 34. Tournament Flow

Tournament functionality can be added after the core match-scoring flow is stable.

```text
Create Tournament
        ↓
Add Teams
        ↓
Create Fixtures
        ↓
Schedule Match
        ↓
Play Match
        ↓
Record Score
        ↓
Match Result
        ↓
Update Points Table
        ↓
Update NRR
        ↓
Next Match
        ↓
Semi Final
        ↓
Final
        ↓
🏆 Champion
```

---

# 35. Recommended Application Modules

```text
Super Score
│
├── Authentication
│   ├── Registration
│   ├── Login
│   ├── Logout
│   ├── Forgot Password
│   └── OTP Verification
│
├── User Profile
│
├── Dashboard
│
├── Teams
│   ├── Team
│   ├── Squad
│   └── Team Players
│
├── Players
│
├── Matches
│   ├── Match Setup
│   ├── Toss
│   ├── Playing XI
│   ├── Innings
│   ├── Overs
│   ├── Deliveries
│   ├── Wickets
│   ├── Extras
│   └── Match Result
│
├── Live Scoring
│
├── Scorecard
│
├── Player Statistics
│
├── Match History
│
├── Tournaments
│   ├── Teams
│   ├── Fixtures
│   ├── Groups
│   ├── Points Table
│   └── Knockouts
│
└── Sharing / Notifications
```

---

# 36. Core Match Data Flow

The central scoring architecture should be based on individual deliveries.

```text
USER
 │
 ├── Register
 │
 └── Login
       │
       ▼
    PROFILE
       │
       ▼
    DASHBOARD
       │
       ├───────────────┐
       ▼               ▼
    TEAMS           MATCHES
       │               │
       ▼               ▼
    PLAYERS       CREATE MATCH
                       │
                       ▼
                 MATCH SETUP
                       │
                       ▼
                 PLAYING XI
                       │
                       ▼
                     TOSS
                       │
                       ▼
                   INNINGS
                       │
                       ▼
                    OVERS
                       │
                       ▼
                  DELIVERIES
                       │
                       ▼
              SCORE CALCULATION
                       │
             ┌─────────┴─────────┐
             ▼                   ▼
        1st INNINGS          2nd INNINGS
             │                   │
             └─────────┬─────────┘
                       ▼
                  MATCH RESULT
                       │
             ┌─────────┼──────────┐
             ▼         ▼          ▼
         SCORECARD   STATS     SHARING
             │
             ▼
        MATCH HISTORY
```

---

# 37. Recommended MVP Development Order

## Phase 1 — Foundation

```text
Registration
    ↓
Login
    ↓
Profile
    ↓
Dashboard
```

## Phase 2 — Teams & Players

```text
Create Team
    ↓
Add Players
    ↓
Manage Squad
```

## Phase 3 — Match Setup

```text
Create Match
    ↓
Select Teams
    ↓
Select Playing XI
    ↓
Toss
```

## Phase 4 — Scoring Engine

```text
Start Innings
    ↓
Delivery
    ↓
Runs / Extras / Wicket
    ↓
Update Score
    ↓
End Over
    ↓
Continue
```

## Phase 5 — Match Completion

```text
1st Innings
    ↓
2nd Innings
    ↓
Result
    ↓
Scorecard
    ↓
Match History
```

## Phase 6 — Advanced Features

```text
Player Statistics
Tournament
Points Table
Live Public Score
Sharing
Notifications
Analytics
```

---

# 38. Critical Architecture Principle

The **Delivery** (individual ball) should be the central scoring record.

```text
Match
  ↓
Innings
  ↓
Over
  ↓
Delivery
  ↓
┌─────────────────────────┐
│ striker                 │
│ non_striker             │
│ bowler                  │
│ runs                    │
│ extras                  │
│ wicket                  │
│ dismissal type          │
│ fielder                 │
│ legal delivery          │
└─────────────────────────┘
          ↓
   Calculated Statistics
          ↓
   Scorecard / Player Stats
          ↓
   Match Result
```

From delivery records, the system should derive:

- Team score
- Batter statistics
- Bowler statistics
- Extras
- Overs
- Partnerships
- Fall of wickets
- Scorecard
- Ball-by-ball history
- Match statistics
- Player career statistics
- Tournament statistics

This approach also makes **undo, ball correction, score recalculation, and historical statistics** much easier to maintain consistently.
