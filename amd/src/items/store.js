// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Items store.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        state: {
            items: [],
            draggingItem: false,
            draggingDegree: false,
        },
        addItem: function(item) {
            item.ord = this.state.items.length;
            this.state.items.push(item);
        },
        addItemAfter: function(index, item) {
            if (index < 0) {
                index = 0;
            }
            item.ord = ++index;

            if (index >= this.state.items.length) {
                this.state.items.push(item);
                return [];
            }
            this.state.items.splice(index, 0, item);

            var itemsToPersist = [];
            for (var i = index + 1; i < this.state.items.length; i++) {
                this.state.items[i].ord = i;
                itemsToPersist.push(this.state.items[i]);
            }
            return itemsToPersist;
        },
        moveItem: function(indexFrom, indexTo) {
            if (indexFrom < 0 || indexFrom >= this.state.items.length) {
                return [];
            }

            if (indexTo < 0) {
                indexTo = 0;
            } else if (indexTo >= this.state.items.length) {
                indexTo = this.state.items.length - 1;
            }

            if (indexFrom === indexTo) {
                return [];
            }

            const itemA = this.state.items[indexFrom];
            const itemB = this.state.items[indexTo];

            itemA.ord = indexTo;
            itemB.ord = indexFrom;

            this.state.items.splice(indexFrom, 1, itemB);
            this.state.items.splice(indexTo, 1, itemA);

            return [ itemA, itemB ];
        },
        deleteItem: function(index) {
            if (index < 0 || index >= this.state.items.length) {
                return [];
            }
            this.state.items.splice(index, 1);

            var itemsToPersist = [];
            for (var i = index; i < this.state.items.length; i++) {
                this.state.items[i].ord = i;
                itemsToPersist.push(this.state.items[i]);
            }
            return itemsToPersist;
        },
        addDegreeToItem: function(item, degree) {
            item.degrees.push(degree);
        },
        addDegreeToItemAfter: function(index, item, degree) {
            var degreesToPersist = [];
            item.degrees.splice(index, 0, degree);
            if (item.degrees.length > index + 1 && item.degrees[index + 1].ord <= degree.ord) {
                var j = 1;
                for (var i = index + 1; i < item.degrees.length; i++) {
                    item.degrees[i].ord = degree.ord + j;
                    degreesToPersist.push(item.degrees[i]);
                    ++j;
                }
            }
            return degreesToPersist;
        },
        deleteDegreeFromItem: function(item, degreeIndex) {
            var deletedDegree = null;
            for (var i = 0; i < item.degrees.length; i++) {
                if (i === degreeIndex) {
                    deletedDegree = item.degrees[i];
                    item.degrees.splice(i, 1);
                    break;
                }
            }
            return deletedDegree;
        },
        startDraggingItem: function() {
            this.state.draggingItem = true;
        },
        stopDraggingItem: function() {
            this.state.draggingItem = false;
        },
        startDraggingDegree: function() {
            this.state.draggingDegree = true;
        },
        stopDraggingDegree: function() {
            this.state.draggingDegree = false;
        }
    };
});
