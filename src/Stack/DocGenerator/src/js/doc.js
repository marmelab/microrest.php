
var Accordion = ReactBootstrap.Accordion;
var Panel = ReactBootstrap.Panel;

var EndpointTitle = React.createClass({
    displayName: 'Endpoints',

    render: function() {
        var method = this.props['method'],
            bsStyle = 'default';

        if ('POST' === method) {
            bsStyle = 'success';
        }
        if ('PUT' === method) {
            bsStyle = 'warning';
        }
        if ('DELETE' === method) {
            bsStyle = 'danger';
        }
        if ('GET' === method) {
            bsStyle = 'success';
        }
        var bsClass = 'label label-' + bsStyle;

        return (
            <span className={ bsClass }>{ method }</span>
        );
    }
});

var Endpoints = React.createClass({
    displayName: 'Endpoints',

    render: function() {
        var panels = this.props.endpoints.map(function(endpoint, i) {
            var endpointKey = this.props.resourceKey + '-endpoint-' + (i + 1),
                divStyle = { paddingLeft: '10px' },
                title = (
                    <div className="container-fluid">
                        <div className="pull-left">
                            <EndpointTitle method={ endpoint['method'] }/>
                        </div>
                        <div className="pull-left" style={ divStyle }>
                            { endpoint.uri }
                        </div>
                        <div className="pull-right">{ endpoint.description }</div>
                    </div>
            );

            return (
                <Panel header={ title } eventKey={ endpointKey }>
                    ...
                </Panel>
            );
        }.bind(this));

        return  (
            <Accordion>
                { panels }
            </Accordion>
        );
    }
});

var Resources = React.createClass({
    displayName: 'Resources',

    render: function() {
        var panels = this.props.resources.map(function(resource, i) {
            var resourceKey = 'resource-' + (i + 1),
                title = ( 
                <h3>{ resource.uri } { resource.description }</h3> 
            );

            return (
                <Panel header={ title } eventKey={ resourceKey }>
                    <Endpoints endpoints={ resource.endpoints } resourceKey={ resourceKey } />
                </Panel>
            );
        });

        return  (
            <Accordion>
                { panels }
            </Accordion>
        );
    }
});

React.render(<Resources resources={ data } />, document.getElementById('resources_container'));