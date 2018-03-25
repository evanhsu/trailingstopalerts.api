import { fromJS } from 'immutable';
// import { StopAlert } from '../objectDefinitions/StopAlert';  // Immutable 'Record'

export const fetchStopAlerts = (token) =>
  // Requires the `redux-thunk` library for making asynchronous calls.
  function (dispatch) {
    dispatch(requestStopAlerts());

    return fetch('/api/alert', {
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    })
      .then((response) => {
        if (response.status === 200) {
          return response.json();
        }
        throw new Error(`Bad HTTP Status: ${response.status}`);
      })
      .then((response) => {
        dispatch(receiveStopAlertsSuccess(response));
      })
      .catch((error) => {
        console.error(error); // eslint-disable-line no-console
        dispatch(receiveStopAlertsFailure(error));
      });
  };

/*
 * Don't call requestStopAlerts() directly - it's dispatched from within `fetchStopAlerts()`
 */
export const REQUEST_STOP_ALERTS = 'REQUEST_STOP_ALERTS';
export const requestStopAlerts = () => ({
  type: REQUEST_STOP_ALERTS,
});

/*
 * Don't call receiveStopAlertsSuccess() directly - it's dispatched from within `fetchStopAlerts()`
 */
export const RECEIVE_STOP_ALERTS_SUCCESS = 'RECEIVE_STOP_ALERTS_SUCCESS';
export const receiveStopAlertsSuccess = (response) => ({
  type: RECEIVE_STOP_ALERTS_SUCCESS,
  payload: fromJS(response),
});

export const RECEIVE_STOP_ALERTS_FAILURE = 'RECEIVE_STOP_ALERTS_FAILURE';
export const receiveStopAlertsFailure = (error) => ({
  type: RECEIVE_STOP_ALERTS_FAILURE,
  error,
});

export const destroyStopAlert = (id, token) => (
  (dispatch) => {
    dispatch(destroyStopAlertRequest(id));

    return fetch(`/api/alert/${id}`, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    })
      .then((response) => {
        if (response.status === 204) {
          dispatch(destroyStopAlertSuccess(id));
        } else {
          throw new Error(`Bad HTTP Status: ${response.status}`);
        }
      })
      .catch((error) => {
        console.error(error); // eslint-disable-line no-console
        dispatch(destroyStopAlertFailure(error));
      });
  }
);

export const DESTROY_STOP_ALERT = 'DESTROY_STOP_ALERT';
export const destroyStopAlertRequest = (id) => ({
  type: DESTROY_STOP_ALERT,
  payload: id,
});

export const DESTROY_STOP_ALERT_SUCCESS = 'DESTROY_STOP_ALERT_SUCCESS';
export const destroyStopAlertSuccess = (id) => ({
  type: DESTROY_STOP_ALERT_SUCCESS,
  payload: id,
});

export const DESTROY_STOP_ALERT_FAILURE = 'DESTROY_STOP_ALERT_FAILURE';
export const destroyStopAlertFailure = (error) => ({
  type: DESTROY_STOP_ALERT_FAILURE,
  payload: error,
});

