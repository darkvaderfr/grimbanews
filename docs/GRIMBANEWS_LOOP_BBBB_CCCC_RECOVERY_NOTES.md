# GrimbaNews — Loop BBBB+CCCC Recovery Notes

**Date:** 2026-05-27
**Owner:** Vader + Claude (operational)

## What happened

Walker BBBB completed cleanly with 60 docs at `0186b33e`. Walker CCCC blocked mid-write at S1447 with 43 of 60 docs on disk (ENOSPC).

## Recovery executed

Manual stage + flip + commit recovered the 43 docs at `7730fefe`. The remaining 17 sprint IDs that CCCC planned to walk but couldn't are tracked for future:

S1447, S1449, S1450, S1451, S1456, S1470, S1489, S2004, S2011, S2012, S2015, S2019, S2020, S2022, S2028, S2033, S2038.

## Lessons logged

- Walker transcript files live in `/private/tmp/claude-501/*/tasks/*.output`. Each walker burns ~500KB-2MB of /tmp.
- Concurrent walker + foreground heavy-writing risks ENOSPC at 99% disk.
- Recovery pattern: untracked walker docs CAN be staged + flipped + committed after disk clears.

## Operator action

Clear `~/Library/Caches` (4.2G observed) or Xcode DerivedData. After disk free > 1G, future walker packs ship cleanly.

## Cross-references

- Walker pack history: Wave LLL/WWW/DDDD/KKKK/XXXX/BBBB/CCCC = 7 walker packs
- Foreground walker waves: UUUU through AAUU
- Total Mythos surrogate docs landed: 345+
