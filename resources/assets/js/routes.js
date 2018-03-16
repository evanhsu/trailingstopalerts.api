import React, { Component } from 'react';
import {Route, Redirect, Switch} from 'react-router-dom';
import Master from './containers/Master';
import Dashboard from './containers/Dashboard';
import AuthManager from './containers/AuthManager';

class Routes extends Component {
    render() {
        return (
            <Master>
                <Switch>
                    <Route path="/login" component={AuthManager}/>
                    <Route path="/dashboard/:stopAlertId?" component={Dashboard}/>
                    {/*<Route path="/account" exact component={Account}/>*/}
                    {/*<Route path="/" exact component={Sales}/>*/}
                    <Redirect from="/" to="/dashboard" />
                </Switch>
            </Master>
        );
    }
};

export default Routes;