import {fromJS} from 'immutable';

export const createStopAlert = (symbol, trailAmount, initialPrice, purchaseDate, token) =>
  // Requires the `redux-thunk` library for making asynchronous calls.
  function (dispatch) {
    dispatch(createStopAlertRequest());

    return fetch(`/api/alert`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        'symbol': symbol,
        'trail_amount': trailAmount,
        'initial_price': initialPrice,
        'purchase_date': purchaseDate,
      })
    })
      .then((response) => {
        if (response.status === 201) {
          return response.json();
        }
        throw new Error(`Bad HTTP Status: ${response.status}`);
      })
      .then((response) => {
        dispatch(createStopAlertSuccess(response));
      })
      .catch((error) => {
        console.error(error); // eslint-disable-line no-console
        dispatch(createStopAlertFailure(error));
      });
  };

export const CREATE_STOP_ALERT = 'CREATE_STOP_ALERT';
export const createStopAlertRequest = () => ({
  type: CREATE_STOP_ALERT,
});

export const CREATE_STOP_ALERT_SUCCESS = 'CREATE_STOP_ALERT_SUCCESS';
export const createStopAlertSuccess = (response) => ({
  type: CREATE_STOP_ALERT_SUCCESS,
  payload: fromJS(response),
});

export const CREATE_STOP_ALERT_FAILURE = 'CREATE_STOP_ALERT_FAILURE';
export const createStopAlertFailure = (error) => ({
  type: CREATE_STOP_ALERT_FAILURE,
  error,
});
