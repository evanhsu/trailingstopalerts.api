import React from 'react';
import { render } from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import thunkMiddleware from 'redux-thunk';
import { BrowserRouter as Router } from 'react-router-dom';
import Immutable from 'immutable';
// import Perf from 'react-addons-perf';
import rootReducer from './reducers';
import Routes from './routes.js';
import registerServiceWorker from './registerServiceWorker';

// React perf addon - for debugging performance issues
// window.Perf = Perf;

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose; // eslint-disable-line no-underscore-dangle
const initialState = Immutable.Map();

const store = createStore(
    rootReducer,
    initialState,
    composeEnhancers(
        applyMiddleware(
            thunkMiddleware, // lets us dispatch() functions
        )
    )
);



const renderApp = () => {
    if (document.querySelector('#root')) {
        render(
            <Provider store={store}>
                <Router>
                    <Routes/>
                </Router>
            </Provider>,
            document.querySelector('#root')
        );
    }
}

renderApp(); // Mount the app to the DOM
registerServiceWorker();

if (module.hot) {
    // Enable Webpack hot module replacement for reducers
    module.hot.accept('./reducers', () => {
        const nextRootReducer = require('./reducers/index');
        store.replaceReducer(nextRootReducer);
    });

    // Hot reload main app
    module.hot.accept('./routes.js', () => {
        renderApp();
    });
}