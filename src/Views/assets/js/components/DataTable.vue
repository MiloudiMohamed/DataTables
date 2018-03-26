<template>
    <div class="card">
        <div class="card-header">
            <strong>{{ response.table }} table</strong>
            <a href="#" class="float-right" v-if="response.allow.creation" @click.prevent="creating.active = !creating.active">
                {{ creating.active ? 'Cancel' : 'New record' }}
            </a>
        </div>

        <div class="card-body">
            <div v-if="creating.active">
                <form action="#" class="form-horizontal" @submit.prevent="store">
                    <div class="form-group" v-for="column in response.updatable">
                        <label class="col-md-3" :for="column">{{ column }}</label>
                        <div class="col-md-6">
                            <input type="text" :id="column" class="form-control" :class="{ 'is-invalid': creating.errors[column] }" v-model="creating.form[column]">
                            <div class="invalid-feedback" v-if="creating.errors[column]">
                                <strong>{{ creating.errors[column][0] }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">Create new record</button>
                        </div>
                    </div>
                </form>
            </div>
            <form action="#" @submit.prevent="getRecords">
                <label for="search">Search</label>
                <div class="row">
                    <div class="form-group col-md-3">
                        <select class="form-control" v-model="search.column">
                            <option :value="column" v-for="column in response.displayable">
                                {{ column }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <select class="form-control" v-model="search.operator">
                            <option value="equals">=</option>
                            <option value="contains">contains</option>
                            <option value="starts_with">starts with</option>
                            <option value="ends_with">ends with</option>
                            <option value="greater_than">greater than</option>
                            <option value="less_than">less than</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <div class="input-group">
                            <input type="text" id="search" v-model="search.value" class="form-control">
                            <span class="input-group-prepend">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </span>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="form-group col-md-10">
                    <label for="filter">Quick search current results</label>
                    <input type="text" id="filter" class="form-control" v-model="quickSearchQuery">
                </div>
                <div class="form-group col-md-2">
                    <label for="limit">Display records</label>
                    <select id="limit" class="form-control" v-model="limit" @change="getRecords">
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="1000">1000</option>
                        <option value="">All</option>
                    </select>
                </div>
            </div>

            <div class=" table-responsive ">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th v-for="column in response.displayable">
                                <span class="sortable" @click="sortBy(column)">{{ column }}</span>

                                <div
                                    class="arrow"
                                    v-if="sort.key === column"
                                    :class="{ 'arrow--asc': sort.order === 'asc', 'arrow--desc': sort.order === 'desc' }"
                                ></div>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in filteredRecords">
                            <td v-for="columnValue, column in record">
                                <template v-if="editing.id === record.id && isUpdatable(column)">
                                    <div class="form-group">
                                        <input type="text" class="form-control" :class="{ 'is-invalid': editing.errors[column] }" v-model="editing.form[column]">
                                        <div class="invalid-feedback" v-if="editing.errors[column]">
                                            <strong>{{ editing.errors[column][0] }}</strong>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    {{ columnValue }}
                                </template>
                            </td>
                            <td>
                                <a href="#" @click.prevent="edit(record)" v-if="editing.id !== record.id">Edit</a>

                                <template v-if="editing.id === record.id">
                                    <a href="#" @click.prevent="update">Save</a><br>
                                    <a href="#" @click.prevent="editing.id = null">Cancel</a>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import queryString from 'query-string'

    export default {
        props: ['endpoint'],
        data () {
            return {
                response: {
                    table: '',
                    displayable: [],
                    records: [],
                    allow: {}
                },
                sort: {
                    key: 'id',
                    order: 'asc'
                },
                limit: 50,
                quickSearchQuery: '',
                editing: {
                    id: null,
                    form: {},
                    errors: []
                },
                search: {
                    value: '',
                    operator: 'equals',
                    column: 'id'
                },
                creating: {
                    active: false,
                    form: {},
                    errors: []
                }
            }
        },
        computed: {
            filteredRecords () {
                let data = this.response.records

                data = data.filter((row) => {
                    return Object.keys(row).some((key) => {
                        return String(row[key]).toLowerCase().indexOf(this.quickSearchQuery.toLowerCase()) > -1
                    })
                })

                if (this.sort.key) {
                    data = _.orderBy(data, (i) => {
                        let value = i[this.sort.key]

                        if (!isNaN(parseFloat(value))) {
                            return parseFloat(value)
                        }

                        return String(i[this.sort.key]).toLowerCase()
                    }, this.sort.order)
                }

                return data
            }
        },
        methods: {
            getRecords () {
                return axios.get(`${this.endpoint}?${this.getQueryParameters()}`).then((response) => {
                    this.response = response.data.data
                })
            },
            getQueryParameters () {
                return queryString.stringify({
                    limit: this.limit,
                    ...this.search
                })
            },
            sortBy (column) {
                this.sort.key = column
                this.sort.order = this.sort.order === 'asc' ? 'desc' : 'asc'
            },
            edit (record) {
                this.editing.errors = []
                this.editing.id = record.id
                this.editing.form = _.pick(record, this.response.updatable)
            },
            isUpdatable (column) {
                return this.response.updatable.includes(column)
            },
            update () {
                axios.patch(`${this.endpoint}/${this.editing.id}`, this.editing.form).then(() => {
                    this.getRecords().then(() => {
                        this.editing.id = null
                        this.editing.form = {}
                    })
                }).catch((error) => {
                    if (error.response.status === 422) {
                        this.editing.errors = error.response.data.errors
                    }
                })
            },
            store () {
                axios.post(`${this.endpoint}`, this.creating.form).then(() => {
                    console.log(this.creating.form)
                    this.getRecords().then(() => {
                        this.creating.active = false
                        this.creating.form = {}
                        this.creating.errors = []
                    })
                }).catch((error) => {
                    if (error.response.status === 422) {
                        this.creating.errors = error.response.data.errors
                    }
                })
            }
        },
        mounted () {
            this.getRecords()
        },
    }
</script>

<style lang="scss">
    .sortable {
        cursor: pointer;
    }

    .arrow {
        display: inline-block;
        vertical-align: middle;
        width: 0;
        height: 0;
        margin-left: 5px;
        opacity: .6;

        &--asc {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-bottom: 4px solid #222;
        }

        &--desc {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 4px solid #222;
        }
    }
</style>
