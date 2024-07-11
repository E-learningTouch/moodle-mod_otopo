<div align="center" markdown>

![Mr Otopo](/pix/mr_otopo.png)

# Otopo

![Moodle Compatibility](https://img.shields.io/badge/Moodle_Compatibility-3.9_to_4.4-green)
![Release](https://img.shields.io/gitlab/v/release/boiteux-c%2Fmoodle-mod_otopo?gitlab_url=https%3A%2F%2Fgitlab.univ-nantes.fr%2F&label=Release
)

:earth_americas: English | [French](docs/fr/README.md)

</div>

***Full documentation for teachers (French only): [wiki.univ-nantes.fr/doku.php?id=madoc:81-otopo](https://wiki.univ-nantes.fr/doku.php?id=madoc:81-otopo).***

## Table of Content

- [About The Plugin](#about-the-project)
- [Installation](#installation)
  - [Moodle Way](#moodle-way)
  - [Manual](#manual)
  - [Using Git](#using-git)

## About The Project

The OTOPO activity module allows teachers to offer their students a self-positioning grid made up of multi-level items.

Students can be asked to self-position themselves on the same grid several times during different sessions, in order to highlight a progression. The teacher can comment on each self-positioning. 

The OTOPO activity has a score made up of items, each with a different score. Each item has a label, a scale and a justification field.

The student can choose either to visualize his or her progress or to self-assess again. If he chooses to self-assess, he sees the various previous answers (scale and justification) and can copy/paste.

The teacher sees a summary of all students, with the grade for each and the date of the last self-positioning. By clicking on a student, he or she can view the student's progress.

## Installation

For more information, see the [official plugin installation guide](https://docs.moodle.org/en/Installing_plugins).

### Moodle Way

1. Download the plugin's latest version or source code.
2. Go to your platform's plugin installation page: `Site administration > Plugins > Install plugins`.
3. Open the plugin file and follow the installation process.

### Manual

1. Download the plugin's latest version or source code.
2. Open the `moodle/mod` directory on your platform.
3. Unzip the plugin into the directory and make sure that the folder containing the plugin files is named `otopo`.

### Using Git

1. Open a terminal and access the `moodle/mod` directory on your platform.
2. Clone the project using the following command: `git clone https://gitlab.univ-nantes.fr/boiteux-c/otopo.git otopo`.
