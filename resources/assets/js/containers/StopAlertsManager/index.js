import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import { connect } from 'react-redux';
import { LinearProgress } from 'material-ui/Progress';
import Accordion from '../../components/Accordion';
import { fetchStopAlerts } from './actions';

class StopAlertsManager extends React.Component {
  componentDidMount() {
    this.props.fetchStopAlerts(this.props.token);
  }

  render() {
    return (
      <div style={{ flexGrow: 1 }}>
        {this.props.loading ? <LinearProgress /> : <Accordion stopAlerts={this.props.stopAlerts} />}
      </div>
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
    token: state.getIn(['auth', 'token']),
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
    fetchStopAlerts: (token) => dispatch(fetchStopAlerts(token)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(StopAlertsManager);
