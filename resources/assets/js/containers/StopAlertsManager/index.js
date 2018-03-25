import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import { connect } from 'react-redux';
import { LinearProgress } from 'material-ui/Progress';
import { Redirect } from 'react-router-dom';
import Accordion from '../../components/Accordion';
import { fetchStopAlerts } from './actions';


class StopAlertsManager extends React.Component {
  componentDidMount() {
    this.props.fetchStopAlerts(this.props.token);
  }

  render() {
    if(!this.props.isLoggedIn) {
      return (<Redirect to="/login"/>);
    }

    return (
      <div style={{ flexGrow: 1 }}>
        {this.props.loading ? <LinearProgress /> : <Accordion onDeleteStopAlert={this.handleDeleteStopAlert} stopAlerts={this.props.stopAlerts.sortBy(( alert ) => alert.get('symbol'))} />}
      </div>
    )
  }
}

StopAlertsManager.propTypes = {
  loading: PropTypes.bool,
  stopAlerts: ImmutablePropTypes.map,
  token: PropTypes.string,
  isLoggedIn: PropTypes.bool,
};

const mapStateToProps = (state) => {
  return {
    loading: state.getIn(['stopAlerts', 'isLoading']),
    stopAlerts: state.getIn(['stopAlerts', 'data']),
    token: state.getIn(['auth', 'token']),
    isLoggedIn: state.getIn(['auth', 'isLoggedIn']),
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
    fetchStopAlerts: (token) => dispatch(fetchStopAlerts(token)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(StopAlertsManager);
