import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { authenticate, setToken } from './actions';
import LoginForm from '../../components/LoginForm';
import { Redirect } from "react-router-dom";

class AuthManager extends React.Component {
  state = {
    email: '',
    password: '',
  };

  componentDidMount() {
    // If there's a token in local storage, we're already logged in. Load this token into State.
    this.setLoggedInStateFromLocalStorage();
  }

  componentDidUpdate() {
    this.saveTokenToLocalStorage();
  }

  handleChange = fieldName => event => {
    this.setState({
      [fieldName]: event.target.value,
    });
  };

  handleLoginClick = () => {
    this.props.authenticate(this.state.email, this.state.password);
  };

  setLoggedInStateFromLocalStorage = () => {
    if(null !== localStorage.getItem('token')) {
      this.props.setToken(localStorage.getItem('token'));
    }
  };

  saveTokenToLocalStorage = () => {
    localStorage.setItem('token', this.props.token);
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
    token: state.getIn(['auth', 'token']),
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
    authenticate: (username, password) => dispatch(authenticate(username, password)),
    setToken: (token) => dispatch(setToken(token)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(AuthManager);
