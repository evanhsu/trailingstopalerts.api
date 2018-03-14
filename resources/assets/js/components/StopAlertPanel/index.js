import React from 'react';
import PropTypes from 'prop-types';
import { withStyles } from 'material-ui/styles';
import ExpansionPanel, {
    ExpansionPanelDetails,
    ExpansionPanelSummary,
} from 'material-ui/ExpansionPanel';
import Typography from 'material-ui/Typography';
import ExpandMoreIcon from 'material-ui-icons/ExpandMore';
import {Paper} from "material-ui";

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
            <ExpansionPanelSummary className={classes.panelSummary} expandIcon={<ExpandMoreIcon />}>
                <Typography className={classes.heading}>{props.stopAlert.symbol}</Typography>
                <Paper elevation={0} className={`${classes.priceGroup} ${classes.highPrice}`}>
                    <Typography className={classes.secondaryHeading}>High: {props.stopAlert.highPrice}</Typography>
                </Paper>
                <Paper elevation={0} className={classes.priceGroup}>
                    <Typography className={classes.secondaryHeading}>Current: {props.stopAlert.price}</Typography>
                </Paper>
                <Paper elevation={0} className={classes.priceGroup}>
                    <Typography className={classes.secondaryHeading}>Stop: {props.stopAlert.triggerPrice}</Typography>
                </Paper>
            </ExpansionPanelSummary>
            <ExpansionPanelDetails>
                <Typography>
                    Nulla facilisi. Phasellus sollicitudin nulla et quam mattis feugiat. Aliquam eget
                    maximus est, id dignissim quam.
                </Typography>
            </ExpansionPanelDetails>
        </ExpansionPanel>
    );
}

StopAlertPanel.propTypes = {
    stopAlert: PropTypes.object,
    expanded: PropTypes.bool,
    onChange: PropTypes.func,
    classes: PropTypes.object,
};

StopAlertPanel.defaultProps = {
    expanded: false,
    onChange: () => ( null ),
};

export default withStyles(styles)(StopAlertPanel);
