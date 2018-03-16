import { combineReducers } from 'redux-immutable';
import stopAlertsReducer from '../containers/StopAlertsManager/stopAlertsReducer';
import authReducer from '../containers/AuthManager/reducer.js';

const rootReducer = combineReducers({
    auth: authReducer,
    stopAlerts: stopAlertsReducer,
});

export default rootReducer;
