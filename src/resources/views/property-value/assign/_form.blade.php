<div id="properties-assign-to-model-modal" class="modal fade" tabindex="-1" role="dialog"
     aria-labelledby="properties-assign-to-model-modal-title" aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            {!! Form::open([
                    'url'  => route('vanilo.property_value.sync', [$for, $forId]),
                    'method' => 'PUT'
                ])
            !!}

            <div class="modal-header">
                <h5 class="modal-title" id="properties-assign-to-model-modal">{{ __('Assign properties') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <table class="table table-condensed table-striped">
                    <tbody>
                    <tr v-for="(assignedProperty, id) in assignedProperties" :id="id">
                        <th>@{{ assignedProperty.property.name }}</th>
                        <td>
                            <select name="propertyValues[]" v-model="assignedProperty.value" @change="onPropertyValueChange($event, id)">
                                <option v-for="value in assignedProperty.values" :value="value.id" v-html="value.title"></option>
                                <optgroup label="{{ __('Missing value?') }}"></optgroup>
                                <option value="+">[+] {{ __('Add value') }}</option>
                            </select>
                        </td>
                        <td>
                            <i class="zmdi zmdi-close text-danger" style="cursor: pointer" @click="removePropertyValue(id)"></i>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <select v-model="selected">
                    <option v-for="(unassignedProperty, id) in unassignedProperties" :value="id">
                        @{{ unassignedProperty.property.name }}
                    </option>
                </select>
                <button class="btn btn-secondary btn-sm" type="button" :disabled="selected == ''"
                        @click="addSelectedPropertyValue">
                    {{ __('Add property') }}
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-dismiss="modal">{{ __('Close') }}</button>
                <button class="btn btn-primary">{{ __('Save properties') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

@include('vanilo::property-value.assign._create_property_value')

@section('scripts')
@parent()
<script>
    new Vue({
        el: '#properties-assign-to-model-modal',
        data: {
            selected: '',
            assignedProperties: {
                @foreach($assignments as $propertyValue)
                "{{ $propertyValue->property->id }}": {
                    "value": "{{ $propertyValue->id }}",
                    "property": {
                        "id":  "{{ $propertyValue->property->id }}",
                        "name": "{{ $propertyValue->property->name }}"
                    },
                    "values": [
                        @foreach($propertyValue->property->values() as $value)
                        {
                            "id": "{{ $value->id }}",
                            "title": "{{ $value->title }}"
                        },
                        @endforeach
                    ]
                },
                @endforeach
            },
            unassignedProperties: {
                @foreach($properties->keyBy('id')->except($assignments->map(function ($propertyValue) {
                        return $propertyValue->property->id;
                })->all()) as $unassignedProperty)
                "{{ $unassignedProperty->id }}": {
                    "value": "",
                    "property": {
                        "id": "{{ $unassignedProperty->id }}",
                        "name": "{{ $unassignedProperty->name }}"
                    },
                    "values": [
                        @foreach($unassignedProperty->values() as $value)
                        {
                            "id": "{{ $value->id }}",
                            "title": "{{ $value->title }}"
                        },
                        @endforeach
                    ]
                },
                @endforeach
            }
        },
        methods: {
            addSelectedPropertyValue() {
                if (this.selected && '' !== this.selected) {
                    var property = this.unassignedProperties[this.selected];
                    if (property) {
                        this.assignedProperties[property.property.id] = property;
                        Vue.delete(this.unassignedProperties, property.property.id);
                    }
                }
            },
            removePropertyValue(propertyId) {
                var property = this.assignedProperties[propertyId];
                if (property) {
                    this.unassignedProperties[propertyId] = property;
                    Vue.delete(this.assignedProperties, propertyId)
                }
            },
            onPropertyValueChange(event, propertyId) {
                var selected = this.assignedProperties[propertyId].value;
                if ('+' !== selected) {
                    return;
                }

                $('#create-property-value').modal('show');
                $('select[name="property_id"]').val(propertyId);
            }
        }
    });
</script>
@endsection