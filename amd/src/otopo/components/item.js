define([
    'mod_otopo/otopo/components/modal',
    'mod_otopo/otopo/components/degrees',
    'mod_otopo/otopo/ajax',
    'mod_otopo/utils'
], function(
    ModalHelp,
    Degrees,
    ajax,
    utils
) {
    return {
        components: {
            'ModalHelp': ModalHelp,
            'Degrees': Degrees
        },
        props: {
            item: { type: Object },
            index: { type: Number }
        },
        data: function() {
            return {
                processChange: utils.debounce(() => this.setUserOtopo()),
                strings: this.$root.$data.strings,
                showModal: false,

                // Current value (ongoing session).
                degree: this.$root.$data.session && this.item.id in this.$root.$data.otopos ?
                    this.$root.$data.otopos[this.item.id].degree : null,

                justification: this.$root.$data.session && this.item.id in this.$root.$data.otopos ?
                    this.$root.$data.otopos[this.item.id].justification : "",

                comment: this.$root.$data.session && this.item.id in this.$root.$data.otopos ?
                    this.$root.$data.otopos[this.item.id].comment : ""
            };
        },

        // Vue lifecycle hook: called after insertion into the DOM.
        // Dans la section mounted() de item.js, modifiez la partie où vous récupérez les données :

        mounted() {
            console.log("Mounted for item ID =", this.item.id, "justification=", this.justification, "degree=", this.degree);
            
            // Vérifier si on doit récupérer les données de la session précédente
            const shouldFetchPrevious = (!this.justification || this.justification.trim() === '') || 
                                    (this.degree === null || this.degree === undefined);
            
            if (shouldFetchPrevious) {
                console.log("Missing data (justification or degree) => let's fetch session-1");
                const currentSession = this.$root.$data.session;
                const prevSession = currentSession - 1;
                
                if (prevSession > 0) {
                    ajax.getUserOtopo(this.$root.$data.otopo, prevSession)
                        .then(prevData => {
                            console.log("prevData for session", prevSession, "=", prevData);
                            let found = prevData.find(obj => obj.item === this.item.id);
                            
                            if (found) {
                                let needToSave = false; // Flag pour savoir si on doit sauvegarder
                                
                                // Reprise de la justification si elle est vide
                                if ((!this.justification || this.justification.trim() === '') && found.justification && found.justification.trim() !== '') {
                                    console.log("Found matching justification: ", found.justification);
                                    this.justification = found.justification;
                                    needToSave = true;
                                }
                                
                                // Reprise du degree s'il est null/undefined
                                if ((this.degree === null || this.degree === undefined) && 
                                    found.degree !== null && found.degree !== undefined) {
                                    console.log("Found matching degree: ", found.degree);
                                    this.degree = found.degree;
                                    needToSave = true;
                                }
                                
                                // Sauvegarder automatiquement si des valeurs ont été récupérées
                                if (needToSave) {
                                    this.$nextTick(() => {
                                        console.log("Auto-saving recovered values: degree=", this.degree, "justification=", this.justification);
                                        this.setUserOtopo();
                                    });
                                }
                            } else {
                                console.log("No matching item found in prevData for item id: " + this.item.id);
                            }
                        })
                        .catch(err => {
                            console.error("Erreur récupération session précédente :", err);
                        });
                }
            }
        },
        
        methods: {
            setUserOtopo() {
                if (this.$root.$data.otopo === null) {
                    // Preview mode => no saving.
                    return;
                }
                ajax.setUserOtopo(
                    this.$root.$data.otopo,
                    this.$root.$data.session,
                    this.item.id,
                    this.degree,
                    this.justification
                );
            },
            degreeChanged(degree) {
                this.degree = degree;
                this.setUserOtopo();
            }
        },

        computed: {
            disabledDegree: function() {
                if (!this.$root.$data.session) {
                    return false;
                }
                return !this.$root.$data.active;
            },
            disabledJustification: function() {
                if (!this.$root.$data.session) {
                    return false;
                }
                return !this.$root.$data.active || !this.degree;
            },
            justificationHtml: function() {
                // Link conversion.
                var expression = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][^\s]*|www\.[^\s]+)/gi;
                return this.justification.replace(expression, '$1'.link('$1'));
            },
            commentHtml: function() {
                if (!this.comment) {
                    return this.strings.noteachercomment;
                }
                var expression = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][^\s]*|www\.[^\s]+)/gi;
                return this.comment.replace(expression, '$1'.link('$1'));
            }
        },

        template: `
            <div class="row mb-3">
                <div class="col-md-12 text-center">
                    <h4 class="font-weight-bold" :style="'color:' + item.color + ';'">{{ item.name }}</h4>
                </div>
                <div class="col-md-4">
                    <Degrees
                        :degree="degree"
                        :itemName="item.name"
                        :color="item.color"
                        :degrees="item.degrees"
                        @changed="degreeChanged"
                        :disabled="disabledDegree"
                    />
                </div>
                <div :class="$root.$data.showComments ? 'col-md-4' : 'col-md-8'">
                    <div class="comment border rounded pt-1 pl-3 pr-3 mb-2 shadow-sm no-print">
                        <div class="input-group mb-3">
                            <textarea
                                v-model="justification"
                                :disabled="disabledJustification"
                                class="form-control border-0"
                                :placeholder="strings.yourjustification"
                                @input="processChange()"
                                rows="5"
                            ></textarea>
                        </div>
                        <small class="text-muted">Veuillez modifier le texte ci-dessus si nécessaire.</small>
                    </div>
                    <div class="comment border rounded pt-1 pl-3 pr-3 mb-2 shadow-sm only-print">
                        <div class="input-group mb-3">
                            <p v-html="justificationHtml"></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button id="show-help-modal"
                                class="bg-light rounded pl-2 pr-3 pt-1 pb-1 border shadow-sm"
                                @click="showModal = true"
                        >
                            <i class="icon fa fa-question" aria-hidden="true"></i>
                            {{ strings.help }}
                        </button>

                        <ModalHelp :text="item.helptext" v-if="showModal" @close="showModal = false" />

                    </div>
                </div>
                <div :class="$root.$data.showComments ? 'col-md-4' : 'd-none'">
                    <div class="comment border rounded pt-1 pl-3 pr-3 mb-2 shadow-sm">
                        <p v-html="commentHtml"></p>
                    </div>
                </div>
            </div>
        `
    };
});
