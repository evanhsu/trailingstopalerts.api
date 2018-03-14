import React from 'react';
import PropTypes from 'prop-types';
import { Paper } from 'material-ui';

function Master(props) {
    return (
        <Paper
            elevation={0}
            style={{
                height: '100%',
            }}
        >
            {/*<PageHeader />*/}
            {props.children}
            {/*<PageFooter />*/}
        </Paper>
    );
}

Master.propTypes = {
    children: PropTypes.node.isRequired,
};

export default Master;
