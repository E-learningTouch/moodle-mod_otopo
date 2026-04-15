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
 * Items vue item.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'mod_otopo/items/store',
    'mod_otopo/items/ajax',
    'mod_otopo/items/components/degree',
    'mod_otopo/color',
    'mod_otopo/utils',
    'core/log'
], function(
    store,
    ajax,
    Degree,
    ColorPicker,
    utils,
    Log
) {
    return {
        components: {
            'Degree': Degree,
            'ColorPicker': ColorPicker
        },
        props: {
            otopo: {
                type: Number
            },
            item: {
                type: Object
            },
            index: {
                type: Number
            }
        },
        mounted: function() {
            this.$refs.name.focus();
        },
        data: function() {
            return {
                nbr: this.item.degrees.length,
                strings: this.$root.$data.strings,
                hasOtopo: this.$root.$data.hasOtopo,
                cmid: this.$root.$data.cmid,
                processChange: utils.debounce(() => this.saveItem()),
                validated: false,
                creationPending: false,
                collapsed: false,
                state: this.$root.$data.state
            };
        },
        methods: {
            deleteItem() {
                if (!this.hasOtopo) {
                    ajax.deleteItem(this.item.id, this.cmid);
                    store.deleteItem(this.index).forEach((item) => {
                        if (item.id) {
                            var itemToPersist = {...item};
                            delete itemToPersist.degrees;
                            ajax.editItem(itemToPersist, this.cmid);
                        }
                    });
                }
            },
            duplicateItem(e) {
                e.preventDefault();
                if (!this.hasOtopo) {
                    const item = {...this.item};
                    delete item.id;
                    item.degrees = [];

                    store.addItemAfter(this.index, item).forEach((nextItem) => {
                        if (nextItem.id) {
                            var itemToPersist = {...nextItem};
                            delete itemToPersist.degrees;
                            ajax.editItem(itemToPersist, this.cmid);
                        }
                    });

                    var itemToCreate = {...item};
                    delete itemToCreate.degrees;
                    ajax.createItem(this.otopo, itemToCreate, this.cmid).then((itemId) => {
                        item.id = itemId;
                        this.item.degrees.forEach((degree) => {
                            var newDegree = {...degree};
                            delete newDegree.id;

                            ajax.createDegree(item.id, newDegree, this.cmid).then((degreeId) => {
                                newDegree.id = degreeId;
                                return true;
                            }).catch(Log.error);

                            item.degrees.push(newDegree);
                        });
                        return true;
                    }).catch(Log.error);
                }
            },
            addDegree(e) {
                e.preventDefault();
                if (!this.hasOtopo) {
                    store.addDegreeToItem(
                        this.item,
                        {
                            'id': null,
                            'name': '',
                            'description': '',
                            'grade': this.nbr + 1,
                            'ord': this.item.degrees.length > 0 ? this.item.degrees[this.item.degrees.length - 1].ord + 1 : 0
                        }
                    );
                    ++this.nbr;
                }
            },
            saveItem() {
                this.validated = true;
                if (this.$refs.formItem.checkValidity()) {
                    const item = {...this.item};
                    delete item.degrees;
                    if (item.id) {
                        ajax.editItem(item, this.cmid);
                    } else {
                        if (!this.creationPending) {
                            this.creationPending = true;
                            delete item.id;
                            ajax.createItem(this.otopo, item, this.cmid).then((itemId) => {
                                this.item.id = itemId;
                                this.creationPending = false;
                                return true;
                            }).catch((error) => {
                                this.creationPending = false;
                                Log.error(error);
                            });
                        }
                    }
                }
            },
            startDrag(evt, index) {
                if (!this.hasOtopo) {
                    evt.dataTransfer.dropEffect = 'move';
                    evt.dataTransfer.effectAllowed = 'move';
                    evt.dataTransfer.setData('itemIndex', index);
                    store.startDraggingItem();
                }
            },
            endDrag() {
                store.stopDraggingItem();
            },
            onDrop(evt, before) {
                evt.preventDefault();
                if (!this.hasOtopo) {
                    const indexFrom = parseInt(evt.dataTransfer.getData('itemIndex'));
                    const indexTo = before ? this.index : this.index + 1;

                    store.moveItem(indexFrom, indexTo).forEach((item) => {
                        if (item.id) {
                            var itemToPersist = {...item};
                            delete itemToPersist.degrees;
                            ajax.editItem(itemToPersist, this.cmid);
                        }
                    });
                }
            },
            onDropDegree(evt) {
                evt.preventDefault();
                if (!this.hasOtopo) {
                    const degreeIndex = parseInt(evt.dataTransfer.getData('degreeIndex'));
                    const itemIndex = parseInt(evt.dataTransfer.getData('itemIndex'));

                    var deletedDegree = store.deleteDegreeFromItem(this.$root.$data.state.items[itemIndex], degreeIndex);
                    deletedDegree.ord = 0;
                    store.addDegreeToItem(this.item, deletedDegree);

                    if (deletedDegree.id) {
                        ajax.deleteDegree(deletedDegree.id, this.cmid);
                        var deletedDegreeToCreate = {...deletedDegree};
                        delete deletedDegreeToCreate.id;
                        ajax.createDegree(this.item.id, deletedDegreeToCreate, this.cmid).then((degreeId) => {
                            store.degreeCreated(deletedDegree, degreeId);
                            return true;
                        }).catch(Log.error);
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
                return this.state.draggingItem;
            },
            isDraggingDegree() {
                return this.state.draggingDegree;
            }
        },
        template: `
            <div class="item">
                <div
                    class="drop-zone"
                    v-bind:class="{'bg-otopo-light': isDragging}"
                    @dragover.prevent
                    @dragenter.prevent
                    @drop="onDrop($event, true)">
                </div>
                <div
                    class="row border rounded bg-light pt-2"
                >
                    <div
                        class="col-md-12"
                        draggable
                        @dragstart="startDrag($event, index)"
                        @dragend="endDrag"
                    >
                        <a class="dropdown-toggle nav-link drag-title" href="#" v-on:click="collapse">
                            <i class="icon fa fa-arrows"></i>
                            <label
                                class="font-weight-bold text-dark"
                                :for="'item_name_' + index"
                            >
                                {{strings.item}} {{index+1}}
                            </label>
                        </a>
                    </div>
                    <div class="col-md-5" v-if="!collapsed">
                        <form ref="formItem" v-bind:class="{ 'was-validated': validated }">
                        <div class="input-group mb-3">
                            <input
                                v-model="item.name"
                                ref="name"
                                class="form-control"
                                type="text"
                                maxlength="255"
                                :title="strings.stringlimit255"
                                :id="'item_name_' + index"
                                :disabled="creationPending"
                                @input="processChange()"
                                required
                            >
                        </div>
                        <label
                            class="font-weight-bold text-dark"
                            :for="'item_color_' + index"
                        >
                            {{strings.chooseitemcolor}}
                        </label>
                        <div class="input-group mb-3" :id="'item_color_colorpicker_' + index">
                            <ColorPicker
                                :color="item.color"
                                v-model="item.color"
                                :id="'item_color_' + index"
                                @input="processChange()"
                                :disabled="creationPending"
                                required
                            />
                        </div>
                        
                        <label
                            class="font-weight-bold text-dark"
                            :for="'item_helptext_text_' + index"
                        >
                            {{strings.chooseitemhelptext}}
                        </label>
                        <div class="input-group mb-3">
                            <textarea
                                v-model="item.helptext"
                                ref="helptext"
                                class="form-control"
                                type="text"
                                :id="'item_helptext_' + index"
                                :disabled="creationPending"
                                @input="processChange()"
                            ></textarea>
                        </div>
                        
                        <button
                            v-on:click="duplicateItem"
                            :disabled="creationPending || hasOtopo"
                            class="btn btn-primary mt-2 mb-3"
                        >
                            {{ strings.duplicateitem }}
                        </button>
                        <button
                            v-on:click="deleteItem"
                            :disabled="creationPending || hasOtopo"
                            class="btn btn-danger mt-2 mb-3"
                        >
                            {{ strings.deleteitem }}
                        </button>
                        </form>
                    </div>
                    <div class="col-md-7" v-if="!collapsed">
                        <Degree
                        v-for="(degree, degreeIndex) in item.degrees"
                        :key="degree.id ? degree.id : 'new_degree' + degreeIndex"
                        :item="item"
                        :degree="degree"
                        :index="degreeIndex"
                        :itemIndex="index"
                        />
                        <a
                            v-on:click="addDegree"
                            :disabled="creationPending"
                            class="mt-2 mb-3 d-inline-block"
                            v-bind:class="{'disabled': hasOtopo}"
                            href="#"
                        >
                            + {{ strings.adddegree }}
                        </a>
                        <div
                            v-if="item.degrees.length === 0"
                            class="drop-zone"
                            v-bind:class="{'bg-otopo-light': isDraggingDegree}"
                            @dropover.prevent
                            @dragenter.prevent
                            @drop="onDropDegree($event)">
                        </div>
                    </div>
                </div>
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
