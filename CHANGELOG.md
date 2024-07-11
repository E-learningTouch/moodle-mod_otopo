# Change Log

All notable changes to this project will be documented in this file.

<u>Key words:</u>
- **Added**: Features, documentation or files are added to improve the plugin or its management.
- **Fixed**: Features, documentation or files are fixed.
- **Changed**: Features, parts of the code or files are changed, altering the plugin's behavior, visual appearance or the project's management.
- **Removed**: Comments, checks, files or other elements deemed unnecessary are removed from the code and project. In the case of comments, it is best to improve them if possible, or to group them together.

When an issue is resolved, ensure it is properly linked to the corresponding issue (E.g: `Fix [#1](/../../issues/1)`).

Additionally, make sure to acknowledge all contributors by adding their names to the [CONTRIBUTORS.md](CONTRIBUTORS.md) file.

## Table of Contents

- [1.0.11](#1.0.11)
- [1.0.10](#1.0.10)
- [1.0.9](#1.0.9)

## 1.0.11

Fixed the remaining *blocking* bugs and compliance issues in this version. Guides and overall project management have also been improved.

### Added

- The [CONTRIBUTORS.md](CONTRIBUTORS.md) file, dedicated to people who have helped improve the plugin.
- The [MAINTAINERS.md](MAINTAINERS.md) file, dedicated to people or entities who have participated or are actively participating in maintaining the plugin.

### Fixed

- Chart labels to make them consistent and avoid some redundant information.
- Compliance with the latest PHP, ESLint and Moodle recommendations.

### Changed

- Improved the [README.md](README.md) and [CHANGELOG.md](CHANGELOG.md) files to make them clearer and easier to understand.

## 1.0.10

Fixed some display bugs, improved user experience and changed various plugin properties.

### Changed

- Session names now match the actual name defined by the teacher when the selected session type is `Open session`.
- Session type selector in the activity parameters to make it more transparent.
- Supported Moodle branches.

### Fixed

- In `My evolution`, the `Radar graphs` were not displaying the data as expected, as the scales did not start at zero.
- Teacher's overall comment was not correctly saved during grading. Remember that the grade must be less than or equal to the maximum displayed.
- Wrong plugin version.

## 1.0.9

Code cleanup.

### Added

- [Privacy API](https://moodledev.io/docs/4.4/apis/subsystems/privacy) integration.
- Comments and headers in template files.
- Disabled the activity header when viewing the grader (no more `Mark as completed` while grading).
- File and class descriptions.
- Missed files descriptors.
- Missed inline comments full-stops.
- Missed login check in some files.
- Missed MOODLE define check in some files.
- Some issue templates and pull request template.
- The contribution file [CONTRIBUTING.md](CONTRIBUTING.md).
- Various checks to ensure the validity of some variables.

### Fixed

- Comments and headers in template files.
- [Deprecated strings](https://github.com/moodle/moodle/blame/main/lang/en/deprecated.txt) since Moodle 4.1.
- Extra line jump in control structures.
- Grader action buttons.
- File and class descriptions.
- Invalid comments.
- Invalid inline structures.
- Invalid lines indentation.
- Invalid uppercases.
- Lignes length (max 132 characters).
- Missed functions visibility.
- Missed space after brackets.
- Missed space in control structures declarations.
- Missed space in variables/keys assignation.
- PHP variable names.

### Removed

- Blanks at the end of lines.
- Commented code (unused code).
- Empty lines.
- PHP closing tags.
- Unnecessary MOODLE define check in classes.
