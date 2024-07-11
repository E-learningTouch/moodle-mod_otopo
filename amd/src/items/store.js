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
            this.state.items.push(item);
        },
        addItemAfter: function(index, item) {
            var itemsToPersist = [];
            this.state.items.splice(index, 0, item);
            if (this.state.items.length > index + 1 && this.state.items[index + 1].ord <= item.ord) {
                var j = 1;
                for (var i = index + 1; i < this.state.items.length; i++) {
                    this.state.items[i].ord = item.ord + j;
                    itemsToPersist.push(this.state.items[i]);
                    ++j;
                }
            }
            return itemsToPersist;
        },
        deleteItem: function(index) {
            var deletedItem = null;
            for (var i = 0; i < this.state.items.length; i++) {
                if (i === index) {
                    deletedItem = this.state.items[i];
                    this.state.items.splice(i, 1);
                    break;
                }
            }
            return deletedItem;
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
