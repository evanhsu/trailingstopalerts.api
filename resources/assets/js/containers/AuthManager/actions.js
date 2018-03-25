import {fromJS} from 'immutable';

export const authenticate = (email, password) =>
  // Requires the `redux-thunk` library for making asynchronous calls.
  function (dispatch) {
    dispatch(requestAuthentication());

    return fetch('/oauth/token', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        'grant_type': 'password',
        'username': email,
        'password': password,
        'client_id': '1',
        'client_secret': 'p252ZUtoeyJX5kcB3HALX78or4esbDlEnqDZWW70',
        'scope': '*',
      })
    })
      .then((response) => {
        if (response.status === 200) {
          return response.json();
        }
        throw new Error(`Bad HTTP Status: ${response.status}`);
      })
      .then((response) => {
        dispatch(authenticationSuccess(response));
      })
      .catch((error) => {
        console.error(error); // eslint-disable-line no-console
        dispatch(authenticationFailure(error));
      });
  };

export const REQUEST_AUTHENTICATION = 'REQUEST_AUTHENTICATION';
export const requestAuthentication = () => ({
  type: REQUEST_AUTHENTICATION,
});

export const AUTHENTICATION_SUCCESS = 'AUTHENTICATION_SUCCESS';
export const authenticationSuccess = (response) => ({
  type: AUTHENTICATION_SUCCESS,
  payload: fromJS(response),
});

export const AUTHENTICATION_FAILURE = 'AUTHENTICATION_FAILURE';
export const authenticationFailure = (error) => ({
  type: AUTHENTICATION_FAILURE,
  error,
});

export const SET_TOKEN = 'SET_TOKEN';
export const setToken = (token) => ({
  type: SET_TOKEN,
  payload: token,
});

export const LOGOUT = 'LOGOUT';
export const logout = () => ({
  type: LOGOUT,
});
