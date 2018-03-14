import { combineReducers } from 'redux-immutable';
import stopAlertsReducer from '../containers/StopAlertsManager/stopAlertsReducer';
// import { authReducer as auth } from '../containers/Auth/authReducer';

const rootReducer = combineReducers({
    // auth,
    stopAlerts: stopAlertsReducer,
});

export default rootReducer;
