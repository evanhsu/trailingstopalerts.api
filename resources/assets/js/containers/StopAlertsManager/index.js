import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import { connect } from 'react-redux';
import { LinearProgress } from 'material-ui/Progress';
import Accordion from '../../components/Accordion';
import { fetchStopAlerts } from './actions';

class StopAlertsManager extends React.Component {
    componentDidMount() {
        this.props.fetchStopAlerts();
    }

    render() {
        return (
            this.props.loading ? <LinearProgress/> : <Accordion stopAlerts={this.props.stopAlerts}/>
        )
    }
}

StopAlertsManager.propTypes = {
    loading: PropTypes.bool,
    stopAlerts: ImmutablePropTypes.list,
};

const mapStateToProps = (state) => {
    return {
        loading: state.getIn(['stopAlerts', 'isLoading']),
        stopAlerts: state.getIn(['stopAlerts', 'data']),
    };
};

const mapDispatchToProps = (dispatch) => {
    return {
        fetchStopAlerts: () => dispatch(fetchStopAlerts()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(StopAlertsManager);
