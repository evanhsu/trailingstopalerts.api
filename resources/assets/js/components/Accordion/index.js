import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import Button from 'material-ui/Button';
import AddIcon from 'material-ui-icons/Add';
import { withStyles } from 'material-ui/styles';
import StopAlertPanel from '../StopAlertPanel';
import {Typography} from "material-ui";

const styles = theme => ({
    root: {},
    accordionRoot: {
        flexGrow: 1,
    },
    button: {

    },
    heading: {
        display: 'flex',
        alignItems: 'center',
        paddingBottom: 15,
    },
    headingText: {
        fontSize: 40,
        paddingRight: 10,
    },
});

class Accordion extends React.Component {
    state = {
        expanded: null,
    };

    alert1 = {
        symbol: 'MSFT',
        highPrice: 1280.24,
        price: 1193.22,
        triggerPrice: 974.09,
        triggered: false,
    };

    alert2 = {
        symbol: 'GOOGL',
        highPrice: 970.09,
        price: 968.55,
        triggerPrice: 902.79,
        triggered: false,
    };

    alert3 = {
        symbol: 'AMZN',
        highPrice: 845.73,
        price: 775.90,
        triggerPrice: 800.48,
        triggered: true,
    };

    handleChange = panel => (event, expanded) => {
        this.setState({
            expanded: expanded ? panel : false,
        });
    };

    render() {
        const { classes } = this.props;
        const { expanded } = this.state;

        return (
            <div className={classes.root}>
                <div className={classes.heading}>
                    <Typography className={classes.headingText}>Alerts</Typography>
                    <Button mini variant="fab" color="secondary" aria-label="add" className={classes.button}>
                        <AddIcon />
                    </Button>
                </div>
                <div className={classes.accordionRoot}>
                    <StopAlertPanel stopAlert={this.alert1} expanded={expanded === 'panel1'} onChange={this.handleChange('panel1')} />
                    <StopAlertPanel stopAlert={this.alert2} expanded={expanded === 'panel2'} onChange={this.handleChange('panel2')} />
                    <StopAlertPanel stopAlert={this.alert3} expanded={expanded === 'panel3'} onChange={this.handleChange('panel3')} />
                </div>
            </div>
        );
    }
}

Accordion.propTypes = {
    classes: PropTypes.object.isRequired,
    stopAlerts: ImmutablePropTypes.list,
};

export default withStyles(styles)(Accordion);