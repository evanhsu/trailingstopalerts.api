import { fromJS } from 'immutable';
import {
  REQUEST_AUTHENTICATION,
  AUTHENTICATION_SUCCESS,
  AUTHENTICATION_FAILURE,
  SET_TOKEN,
  LOGOUT,
} from './actions';

const initialState = fromJS({
  user: null,
  token: null,
  isLoggedIn: false,
  isLoading: false,
});

/* eslint-disable no-case-declarations */
function authReducer(state = initialState, action) {
  switch (action.type) {
    case AUTHENTICATION_SUCCESS:
      return state
        .set('token', action.payload.get('access_token'))
        .set('isLoggedIn', true)
        .set('isLoading', false);

    case AUTHENTICATION_FAILURE:
      return state
        .set('isLoading', false);

    case REQUEST_AUTHENTICATION:
      return state
        .set('isLoading', true);

    case SET_TOKEN:
      return state
        .set('token', action.payload)
        .set('isLoggedIn', true);

    case LOGOUT:
      return initialState;

    default:
      return state;
  }
}

export default authReducer;
