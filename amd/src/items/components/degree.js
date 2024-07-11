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
 * Items vue degree.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'mod_otopo/items/store',
    'mod_otopo/items/ajax',
    'mod_otopo/utils',
    'core/log'
], function(
    store,
    ajax,
    utils,
    Log
) {
    return {
        props: {
            item: {
                type: Object
            },
            itemIndex: {
                type: Number,
            },
            degree: {
                type: Object
            },
            index: {
                type: Number
            }
        },
        data: function() {
            return {
                errors: [],
                strings: this.$root.$data.strings,
                hasOtopo: this.$root.$data.hasOtopo,
                cmid: this.$root.$data.cmid,
                processChange: utils.debounce(() => this.saveDegree()),
                validated: false,
                creationPending: false,
                collapsed: false,
                state: this.$root.$data.state
            };
        },
        mounted: function() {
            this.$refs.name.focus();
        },
        methods: {
            deleteDegree(e) {
                e.preventDefault();
                if (!this.hasOtopo) {
                    store.deleteDegreeFromItem(this.item, this.index);
                    ajax.deleteDegree(this.degree.id, this.cmid);
                }
            },
            saveDegree() {
                this.validated = true;
                if (this.$refs.formDegree.checkValidity()) {
                    const degree = {...this.degree};
                    if (degree.id) {
                        ajax.editDegree(degree, this.cmid);
                    } else {
                        if (!this.creationPending) {
                            this.creationPending = true;
                            delete degree.id;
                            ajax.createDegree(this.item.id, degree, this.cmid).then((degreeId) => {
                                this.degree.id = degreeId;
                                this.creationPending = false;
                                return true;
                            }).catch(Log.error);
                        }
                    }
                }
            },
            startDrag(evt, itemIndex, index) {
                if (!this.hasOtopo) {
                    evt.dataTransfer.dropEffect = 'move';
                    evt.dataTransfer.effectAllowed = 'move';
                    evt.dataTransfer.setData('degreeIndex', index);
                    evt.dataTransfer.setData('itemIndex', itemIndex);
                    store.startDraggingDegree();
                }
            },
            endDrag() {
                store.stopDraggingDegree();
            },
            onDrop(evt, before) {
                evt.preventDefault();
                if (!this.hasOtopo) {
                    const degreeIndex = parseInt(evt.dataTransfer.getData('degreeIndex'));
                    const itemIndex = parseInt(evt.dataTransfer.getData('itemIndex'));
                    const testDegreeIndex = before ? degreeIndex + 1 : degreeIndex;
                    const testIndex = before ? this.index : this.index + 1;
                    if ((itemIndex === this.itemIndex && degreeIndex != this.index && testDegreeIndex != testIndex)
                       || itemIndex !== this.itemIndex) {
                        var deletedDegree = store.deleteDegreeFromItem(this.$root.$data.state.items[itemIndex], degreeIndex);
                        deletedDegree.ord = before ? this.degree.ord : this.degree.ord + 1;
                        var degreesToPersist = store.addDegreeToItemAfter(this.index, this.item, deletedDegree);
                        degreesToPersist.forEach((degree) => {
                            if (degree.id) {
                                ajax.editDegree(degree, this.cmid);
                            }
                        });
                        if (deletedDegree.id) {
                            if (itemIndex !== this.itemIndex) {
                                ajax.deleteDegree(deletedDegree.id, this.cmid);
                                var deletedDegreeToCreate = {...deletedDegree};
                                delete deletedDegreeToCreate.id;
                                ajax.createDegree(this.item.id, deletedDegreeToCreate, this.cmid).then((degreeId) => {
                                    store.degreeCreated(deletedDegree, degreeId);
                                    return true;
                                }).catch(Log.error);
                            } else {
                                ajax.editDegree(deletedDegree, this.cmid);
                            }
                        }
                    }
                }
            },
            collapse(e) {
                e.preventDefault();
                this.collapsed = !this.collapsed;
            }
        },
        computed: {
            isDragging() {
                return this.state.draggingDegree;
            }
        },
        template: `
            <div class="degree">
                <div
                    class="drop-zone"
                    v-bind:class="{'bg-otopo-light': isDragging}"
                    @dragover.prevent
                    @dragenter.prevent
                    @drop="onDrop($event, true)">
                </div>
                <form ref="formDegree" v-bind:class="{ 'was-validated': validated }">
                    <div
                        class="row border rounded bg-white mb-2 pt-2"
                    >
                        <div
                            class="col-md-12"
                            draggable
                            @dragstart="startDrag($event, itemIndex, index)"
                            @dragend="endDrag"
                        >
                            <a class="dropdown-toggle nav-link drag-title" href="#" v-on:click="collapse">
                                <i class="icon fa fa-arrows"></i>
                                <label
                                    class="font-weight-bold text-dark"
                                    :for="'item_' + itemIndex + '_degree_name_' + index"
                                >
                                    {{strings.degree}} {{index+1}}
                                </label>
                            </a>
                        </div>
                        <div class="col-md-9" v-if="!collapsed">
                            <div class="input-group mb-3">
                                <input
                                    v-model="degree.name"
                                    class="form-control"
                                    ref="name"
                                    type="text"
                                    maxlength="255"
                                    :title="strings.stringlimit255"
                                    :id="'item_' + itemIndex + '_degree_name_' + index"
                                    @input="processChange()"
                                    :disabled="creationPending"
                                    required
                                >
                            </div>
                            <div class="input-group mb-3">
                                <textarea
                                    v-model="degree.description"
                                    class="form-control"
                                    type="text" :id="'item_' + itemIndex + '_degree_description_' + index"
                                    @input="processChange()"
                                    :disabled="creationPending"
                                ></textarea>
                            </div>
                        </div>
                        <div class="col-md-3" v-if="!collapsed">
                            <label
                                class="font-weight-bold text-dark"
                                :for="'item_' + itemIndex + '_degree_grade_' + index"
                            >
                                {{strings.degreegrade}}
                            </label>
                            <div class="input-group mb-3">
                                <input
                                    v-model="degree.grade"
                                    class="form-control"
                                    type="text" :id="'item_' + itemIndex + '_degree_grade_' + index"
                                    @input="processChange()"
                                    :disabled="creationPending || hasOtopo"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col-md-12" v-if="!collapsed">
                            <a
                            v-on:click="deleteDegree"
                            class="mb-3 d-inline-block"
                            v-bind:class="{'disabled': hasOtopo}"
                            href="#" >- {{ strings.deletedegree }}
                            </a>
                        </div>
                    </div>
                </form>
                <div
                    class="drop-zone"
                    v-bind:class="{'bg-otopo-light': isDragging}"
                    @dragover.prevent
                    @dragenter.prevent
                    @drop="onDrop($event, false)">
                </div>
            </div>
        `
    };
});
