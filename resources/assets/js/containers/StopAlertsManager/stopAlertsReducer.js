import { fromJS } from 'immutable';
import {
    RECEIVE_STOP_ALERTS_SUCCESS,
    RECEIVE_STOP_ALERTS_FAILURE,
    REQUEST_STOP_ALERTS,
} from './actions';

const initialState = fromJS({
    selectedId: null,
    data: [],
    isLoading: true,
});

/* eslint-disable no-case-declarations */
function stopAlertsReducer(state = initialState, action) {
    switch (action.type) {
        case RECEIVE_STOP_ALERTS_SUCCESS:
            return state
                .set('data', action.payload.data)
                .set('isLoading', false);

        case RECEIVE_STOP_ALERTS_FAILURE:
            return state
                .set('isLoading', false);

        case REQUEST_STOP_ALERTS:
            return state.set('isLoading', true);

        default:
            return state;
    }
}

export default stopAlertsReducer;
