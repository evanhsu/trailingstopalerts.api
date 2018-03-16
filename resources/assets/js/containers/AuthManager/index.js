import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { authenticate } from './actions';
import LoginForm from '../../components/LoginForm';
import { Redirect } from "react-router-dom";

class AuthManager extends React.Component {
  state = {
    email: '',
    password: '',
  };

  handleChange = fieldName => event => {
    this.setState({
      [fieldName]: event.target.value,
    });
  };

  handleLoginClick = () => {
    this.props.authenticate(this.state.email, this.state.password);
  };

  render() {
    // If this.props.isLoggedIn redirect...
    return (
      this.props.isLoggedIn ? <Redirect to="/dashboard"/> : <LoginForm onLoginClick={this.handleLoginClick} onFieldChange={this.handleChange}/>
    );
  };
}

AuthManager.propTypes = {
  isLoggedIn: PropTypes.bool,
  authenticate: PropTypes.func.isRequired,
};

const mapStateToProps = (state) => {
  return {
    isLoggedIn: state.getIn(['auth', 'isLoggedIn']),
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
    authenticate: (username, password) => dispatch(authenticate(username, password)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(AuthManager);
