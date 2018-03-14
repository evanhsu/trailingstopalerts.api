import React from 'react';
import {Grid} from "material-ui";
import StopAlertsManager from '../StopAlertsManager';

class Dashboard extends React.Component {

    render() {
        return (
            <div style={{ flexGrow: 1 }}>
                <Grid container spacing={24} alignItems={'center'} justify={'flex-start'} direction={'column'}>
                    <Grid item xs={12} md={8}>
                        <StopAlertsManager/>
                    </Grid>
                </Grid>
            </div>
        );
    }
}

export default Dashboard;
