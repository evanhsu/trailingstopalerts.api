import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import { withStyles } from 'material-ui/styles';
import ExpansionPanel, {
    ExpansionPanelDetails,
    ExpansionPanelSummary,
} from 'material-ui/ExpansionPanel';
import Typography from 'material-ui/Typography';
import EditIcon from 'material-ui-icons/Edit';
import {Paper} from "material-ui";
import StopAlertEditForm from '../../containers/StopAlertEditForm';

const styles = theme => ({
    root: {
        flexGrow: 1,
    },
    heading: {
        fontSize: theme.typography.pxToRem(20),
        flexBasis: '33.33%',
        flexShrink: 0,
        fontWeight: 'bold',
    },
    secondaryHeading: {
        fontSize: theme.typography.pxToRem(15),
        color: theme.palette.text.secondary,
    },
    priceGroup: {
        marginLeft: 10,
        marginRight: 10,
        paddingLeft: 10,
        paddingRight: 10,
    },
    highPrice: {
        backgroundColor: '#31c632',
    },
    currentPrice: {
        backgroundColor: '#777777',
    },
    panelSummary: {
        minHeight: 100,
    },
    triggerPrice: {
        backgroundColor: '#c63132',
    },
});

function StopAlertPanel(props) {
    const { classes } = props;

    return (
        <ExpansionPanel expanded={props.expanded} onChange={props.onChange}>
            <ExpansionPanelSummary className={classes.panelSummary} expandIcon={<EditIcon />}>
                <Typography className={classes.heading}>{props.stopAlert.get('symbol')}</Typography>
                <Paper elevation={0} className={`${classes.priceGroup} ${classes.highPrice}`}>
                    <Typography className={classes.secondaryHeading}>High: {props.stopAlert.get('high_price')}</Typography>
                </Paper>
                <Paper elevation={0} className={classes.priceGroup}>
                    <Typography className={classes.secondaryHeading}>Stop: {props.stopAlert.get('trigger_price')}</Typography>
                </Paper>
            </ExpansionPanelSummary>
            <ExpansionPanelDetails>
              <StopAlertEditForm stopAlert={props.stopAlert} />
            </ExpansionPanelDetails>
        </ExpansionPanel>
    );
}

StopAlertPanel.propTypes = {
    stopAlert: ImmutablePropTypes.map,
    expanded: PropTypes.bool,
    onChange: PropTypes.func,
    classes: PropTypes.object,
};

StopAlertPanel.defaultProps = {
    expanded: false,
    onChange: () => ( null ),
};

export default withStyles(styles)(StopAlertPanel);
