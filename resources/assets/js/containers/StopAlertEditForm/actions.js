import {fromJS} from 'immutable';

export const updateStopAlert = (id, trailAmount, initialPrice, purchaseDate, token) =>
  // Requires the `redux-thunk` library for making asynchronous calls.
  function (dispatch) {
    dispatch(updateStopAlertRequest());

    return fetch(`/api/alert/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        'id': id,
        'trail_amount': trailAmount,
        'initial_price': initialPrice,
        'purchase_date': purchaseDate,
      })
    })
      .then((response) => {
        if (response.status === 200) {
          return response.json();
        }
        throw new Error(`Bad HTTP Status: ${response.status}`);
      })
      .then((response) => {
        dispatch(updateStopAlertSuccess(response));
      })
      .catch((error) => {
        console.error(error); // eslint-disable-line no-console
        dispatch(updateStopAlertFailure(error));
      });
  };

export const UPDATE_STOP_ALERT = 'UPDATE_STOP_ALERT';
export const updateStopAlertRequest = () => ({
  type: UPDATE_STOP_ALERT,
});

export const UPDATE_STOP_ALERT_SUCCESS = 'UPDATE_STOP_ALERT_SUCCESS';
export const updateStopAlertSuccess = (response) => ({
  type: UPDATE_STOP_ALERT_SUCCESS,
  payload: fromJS(response),
});

export const UPDATE_STOP_ALERT_FAILURE = 'UPDATE_STOP_ALERT_FAILURE';
export const updateStopAlertFailure = (error) => ({
  type: UPDATE_STOP_ALERT_FAILURE,
  error,
});
