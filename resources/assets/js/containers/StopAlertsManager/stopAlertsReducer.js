import { fromJS, Map } from 'immutable';
import {
  RECEIVE_STOP_ALERTS_SUCCESS,
  RECEIVE_STOP_ALERTS_FAILURE,
  REQUEST_STOP_ALERTS,
  DESTROY_STOP_ALERT_SUCCESS,
} from './actions';
import {
  UPDATE_STOP_ALERT_SUCCESS,
  UPDATE_STOP_ALERT_FAILURE,
} from "../StopAlertEditForm/actions";
import {
  CREATE_STOP_ALERT,
  CREATE_STOP_ALERT_SUCCESS,
  CREATE_STOP_ALERT_FAILURE,
} from "../StopAlertCreateForm/actions";

const initialState = new Map({
  selectedId: null,
  data: new Map(),
  isLoading: true,
  createActionIsPending: false,
});

/* eslint-disable no-case-declarations */
function stopAlertsReducer(state = initialState, action) {
  switch (action.type) {
    case RECEIVE_STOP_ALERTS_SUCCESS:
      return state
        .set('isLoading', false)
        .set('data', action.payload.get('data').reduce(
          (lookup, alert) => (
            lookup.set(alert.get('id'), alert)
          ), new Map()
        ));

    case RECEIVE_STOP_ALERTS_FAILURE:
      return state
        .set('isLoading', false);

    case REQUEST_STOP_ALERTS:
      return state.set('isLoading', true);

    case UPDATE_STOP_ALERT_SUCCESS:
      return state.setIn(['data', action.payload.getIn(['data', 'id'])], action.payload.get('data'));

    case UPDATE_STOP_ALERT_FAILURE:
      return state;

    case CREATE_STOP_ALERT:
      return state.set('createActionIsPending', true);

    case CREATE_STOP_ALERT_SUCCESS:
      return state
        .setIn(['data', action.payload.getIn(['data', 'id'])], action.payload.get('data'))
        .set('createActionIsPending', false);

    case CREATE_STOP_ALERT_FAILURE:
      return state.set('createActionIsPending', false);

    case DESTROY_STOP_ALERT_SUCCESS:
      return state.deleteIn(['data', action.payload]);

    default:
      return state;
  }
}

export default stopAlertsReducer;
