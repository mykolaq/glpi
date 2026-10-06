# Real GLPI screenshots for the form action separation PR

Captured on 2026-10-06 in the existing local GLPI Docker development instance (source HEAD 8f4926a), using the exact buttons.html.twig from branch codex/separate-destructive-form-actions, commit be9898a912. This is integration testing of the changed template in the existing instance, not a deployment of the complete main branch.

The PNGs are actual browser captures in Russian with the darker theme. The Symfony debug toolbar is collapsed. Pages are scrolled to the action panel. Desktop before and after use the same existing computer.

- 01-before-desktop.png: original template, 1440 x 1000 viewport.
- 02-after-desktop.png: changed template, 1440 x 1000.
- 03-after-tablet.png: changed template, 768 x 1024.
- 04-after-phone-390.png: changed template, 390 x 844.
- 05-after-phone-360.png: changed template, 360 x 800.
- 06-deleted-desktop.png: temporary computer in trash, 1440 x 1000.
- 07-deleted-phone-360.png: temporary computer in trash, 360 x 800.

Testing performed: existing computer, new computer, and computer in trash, in light and darker themes at all four viewport sizes (24 layouts). Action panel bounds, horizontal overflow and button intersections were checked. Pressing Enter in the name field selects update in the existing form and add in the new form. These are browser viewport tests, not physical device or Safari tests.

At 360 px, the save action and destructive action wrap to separate rows. On a trashed computer, save and restore wrap above the permanent deletion group; the keep-devices switch stays inside that group.

Known unrelated issue: a long trashed-computer name with its deleted status badge can overflow the page heading by about 13 px at 360 px. This also reproduced with the original template. No action panel overflow occurred.

Temporary computers were permanently removed after testing. The original Docker template and its permissions were restored and the application cache was cleared. The code branch was not changed by this validation.

Screenshots and browser checks were prepared with assistance from OpenAI Codex. No full application test suite was run for this visual validation.