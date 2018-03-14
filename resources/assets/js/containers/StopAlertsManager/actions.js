import {fromJS} from 'immutable';
// import { StopAlert } from '../objectDefinitions/StopAlert';  // Immutable 'Record'

export const fetchStopAlerts = () =>
    // Requires the `redux-thunk` library for making asynchronous calls.
    function (dispatch) {
        dispatch(requestStopAlerts());

        return fetch('/api/alert', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
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
